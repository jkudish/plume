<?php

declare(strict_types=1);

namespace Plume\Data;

use Plume\Contracts\XApiProvider;

class User
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly string $username,
        public readonly ?string $description = null,
        public readonly ?string $location = null,
        public readonly ?string $url = null,
        public readonly ?string $profileImageUrl = null,
        public readonly ?string $profileBannerUrl = null,
        public readonly ?string $createdAt = null,
        public readonly ?string $pinnedTweetId = null,
        public readonly bool $protected = false,
        public readonly bool $verified = false,
        public readonly ?string $verifiedType = null,
        public readonly ?UserMetrics $publicMetrics = null,
        /** @var array<string, mixed> */
        public readonly array $entities = [],
        protected ?XApiProvider $provider = null,
    ) {}

    public function withProvider(XApiProvider $provider): static
    {
        $clone = clone $this;
        $clone->provider = $provider;

        return $clone;
    }

    public function follow(string $actorUserId): void
    {
        $this->provider()->follow($actorUserId, $this->id);
    }

    public function unfollow(string $actorUserId): void
    {
        $this->provider()->unfollow($actorUserId, $this->id);
    }

    public function block(string $actorUserId): void
    {
        $this->provider()->block($actorUserId, $this->id);
    }

    public function unblock(string $actorUserId): void
    {
        $this->provider()->unblock($actorUserId, $this->id);
    }

    public function mute(string $actorUserId): void
    {
        $this->provider()->mute($actorUserId, $this->id);
    }

    public function unmute(string $actorUserId): void
    {
        $this->provider()->unmute($actorUserId, $this->id);
    }

    protected function provider(): XApiProvider
    {
        return $this->provider ?? throw new \LogicException('User requires an XApiProvider. Call withProvider() first.');
    }
}
