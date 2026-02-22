<?php

declare(strict_types=1);

namespace Plume\Http;

use Closure;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Plume\Exceptions\AuthenticationException;
use Plume\Exceptions\RateLimitException;
use Plume\Exceptions\ValidationException;
use Plume\Exceptions\XApiException;

class XHttpClient
{
    private bool $hasAttemptedRefresh = false;

    public function __construct(
        protected string $baseUrl,
        protected int $timeout,
        protected ?string $bearerToken = null,
        protected ?string $accessToken = null,
        protected ?string $refreshToken = null,
        protected ?string $expiresAt = null,
        protected ?string $clientId = null,
        protected ?string $clientSecret = null,
        protected ?Closure $tokenRefreshedCallback = null,
    ) {}

    public function withUserTokens(?string $accessToken, ?string $refreshToken = null, ?string $expiresAt = null): static
    {
        $clone = clone $this;
        $clone->accessToken = $accessToken;
        $clone->refreshToken = $refreshToken;
        $clone->expiresAt = $expiresAt;
        $clone->hasAttemptedRefresh = false;

        return $clone;
    }

    public function withTokenRefreshedCallback(?Closure $callback): static
    {
        $clone = clone $this;
        $clone->tokenRefreshedCallback = $callback;

        return $clone;
    }

    /**
     * @param  array<string, mixed>  $query
     * @return array<string, mixed>
     */
    public function get(string $path, array $query = []): array
    {
        return $this->request('get', $path, $query);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function post(string $path, array $data = []): array
    {
        return $this->request('post', $path, $data);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function put(string $path, array $data = []): array
    {
        return $this->request('put', $path, $data);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function delete(string $path, array $data = []): array
    {
        return $this->request('delete', $path, $data);
    }

    protected function http(): PendingRequest
    {
        $request = Http::baseUrl($this->baseUrl)
            ->timeout($this->timeout)
            ->acceptJson();

        $token = $this->accessToken ?? $this->bearerToken;

        if ($token !== null && $token !== '') {
            $request->withToken($token);
        }

        return $request;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function request(string $method, string $path, array $data = []): array
    {
        $response = $this->http()->{$method}($path, $data);

        if ($response->status() === 401 && ! $this->hasAttemptedRefresh && $this->refreshToken !== null) {
            $refreshed = $this->attemptTokenRefresh();

            if ($refreshed) {
                return $this->request($method, $path, $data);
            }
        }

        if ($response->failed()) {
            $this->throwForResponse($response);
        }

        return $response->json() ?? [];
    }

    protected function attemptTokenRefresh(): bool
    {
        $this->hasAttemptedRefresh = true;

        if ($this->clientId === null) {
            return false;
        }

        $request = Http::asForm();

        // Auto-detect: use Basic Auth if client_secret is configured (confidential client)
        if ($this->clientSecret !== null && $this->clientSecret !== '') {
            $request->withBasicAuth($this->clientId, $this->clientSecret);
        }

        $response = $request->post($this->baseUrl.'/2/oauth2/token', [
            'grant_type' => 'refresh_token',
            'refresh_token' => $this->refreshToken,
            'client_id' => $this->clientId,
        ]);

        if ($response->failed()) {
            return false;
        }

        /** @var array{access_token: string, refresh_token?: string, expires_in?: int} $data */
        $data = $response->json();

        $this->accessToken = $data['access_token'];
        $this->refreshToken = $data['refresh_token'] ?? $this->refreshToken;

        $expiresIn = $data['expires_in'] ?? null;
        $this->expiresAt = $expiresIn !== null
            ? now()->addSeconds($expiresIn)->toIso8601String()
            : null;

        if ($this->tokenRefreshedCallback !== null) {
            ($this->tokenRefreshedCallback)([
                'access_token' => $this->accessToken,
                'refresh_token' => $this->refreshToken,
                'expires_at' => $this->expiresAt,
            ]);
        }

        return true;
    }

    /**
     * @return array<string, string>
     */
    protected function extractRateLimitHeaders(Response $response): array
    {
        $headers = [];

        foreach (['x-rate-limit-limit', 'x-rate-limit-remaining', 'x-rate-limit-reset'] as $header) {
            $value = $response->header($header);
            if ($value !== null && $value !== '') {
                $headers[$header] = $value;
            }
        }

        return $headers;
    }

    protected function throwForResponse(Response $response): never
    {
        $status = $response->status();
        $body = $response->json() ?? [];
        $rateLimitHeaders = $this->extractRateLimitHeaders($response);

        /** @var string $message */
        $message = $body['detail'] ?? $body['title'] ?? $body['error'] ?? 'X API error';

        if ($status === 429) {
            throw new RateLimitException($message, $body, $rateLimitHeaders);
        }

        if ($status === 401 || $status === 403) {
            throw new AuthenticationException($message, $status, $body, $rateLimitHeaders);
        }

        if ($status === 400) {
            throw new ValidationException($message, $status, $body, $rateLimitHeaders);
        }

        throw new XApiException($message, $status, $body, $rateLimitHeaders);
    }
}
