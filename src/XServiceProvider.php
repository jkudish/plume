<?php

declare(strict_types=1);

namespace Plume;

use Illuminate\Foundation\Console\AboutCommand;
use Illuminate\Support\ServiceProvider;
use Plume\Contracts\XApiProvider;
use Plume\Http\XHttpClient;

class XServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/x.php', 'x');

        $this->app->singleton(XHttpClient::class, function ($app): XHttpClient {
            $bearerToken = config('x.bearer_token');
            $clientId = config('x.client_id');
            $clientSecret = config('x.client_secret');
            $tokenRefreshedCallback = $app->bound('x.token_refreshed')
                ? $app->make('x.token_refreshed')
                : null;

            return new XHttpClient(
                baseUrl: (string) config('x.base_url', 'https://api.x.com'),
                timeout: (int) config('x.timeout', 30),
                bearerToken: is_string($bearerToken) ? $bearerToken : null,
                clientId: is_string($clientId) ? $clientId : null,
                clientSecret: is_string($clientSecret) ? $clientSecret : null,
                tokenRefreshedCallback: $tokenRefreshedCallback instanceof \Closure ? $tokenRefreshedCallback : null,
            );
        });

        $this->app->singleton(XApiProvider::class, fn ($app): XApiClient => new XApiClient(
            http: $app->make(XHttpClient::class),
        ));
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/x.php' => config_path('x.php'),
            ], 'x-config');
        }

        $this->app->tag([
            \Plume\Ai\Tools\PlumeFetchTweetTool::class,
            \Plume\Ai\Tools\PlumePostTweetTool::class,
            \Plume\Ai\Tools\PlumeSearchTool::class,
            \Plume\Ai\Tools\PlumeHomeTimelineTool::class,
            \Plume\Ai\Tools\PlumeMentionsTool::class,
            \Plume\Ai\Tools\PlumeMyTimelineTool::class,
            \Plume\Ai\Tools\PlumeLikeTool::class,
            \Plume\Ai\Tools\PlumeRetweetTool::class,
            \Plume\Ai\Tools\PlumeBookmarkTool::class,
            \Plume\Ai\Tools\PlumeBookmarksTool::class,
            \Plume\Ai\Tools\PlumeFollowTool::class,
            \Plume\Ai\Tools\PlumeFollowersTool::class,
            \Plume\Ai\Tools\PlumeFollowingTool::class,
            \Plume\Ai\Tools\PlumeProfileTool::class,
            \Plume\Ai\Tools\PlumeUploadMediaTool::class,
        ], 'ai-tools');

        AboutCommand::add('X API', fn (): array => [
            'Base URL' => (string) config('x.base_url'),
            'Timeout' => (string) config('x.timeout').'s',
            'Bearer Token' => config('x.bearer_token') !== null ? 'Configured' : 'Not set',
            'Client ID' => config('x.client_id') !== null ? 'Configured' : 'Not set',
        ]);
    }
}
