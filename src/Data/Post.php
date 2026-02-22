<?php

declare(strict_types=1);

namespace Plume\Data;

use Plume\Contracts\XApiProvider;

class Post
{
    public function __construct(
        public readonly string $id,
        public readonly string $text,
        public readonly ?string $authorId = null,
        public readonly ?string $conversationId = null,
        public readonly ?string $inReplyToUserId = null,
        public readonly ?string $createdAt = null,
        public readonly ?string $lang = null,
        public readonly ?string $source = null,
        public readonly ?string $replySettings = null,
        public readonly bool $possiblySensitive = false,
        public readonly ?PostMetrics $publicMetrics = null,
        /** @var array<int, array{type: string, id: string}> */
        public readonly array $referencedTweets = [],
        /** @var array<string, mixed> */
        public readonly array $entities = [],
        /** @var array<string, mixed> */
        public readonly array $attachments = [],
        public readonly ?Includes $includes = null,
        protected ?XApiProvider $provider = null,
    ) {}

    public function withProvider(XApiProvider $provider): static
    {
        $clone = clone $this;
        $clone->provider = $provider;

        return $clone;
    }

    public function like(string $userId): void
    {
        $this->provider()->like($userId, $this->id);
    }

    public function unlike(string $userId): void
    {
        $this->provider()->unlike($userId, $this->id);
    }

    public function retweet(string $userId): void
    {
        $this->provider()->retweet($userId, $this->id);
    }

    public function undoRetweet(string $userId): void
    {
        $this->provider()->undoRetweet($userId, $this->id);
    }

    public function bookmark(string $userId): void
    {
        $this->provider()->bookmark($userId, $this->id);
    }

    public function removeBookmark(string $userId): void
    {
        $this->provider()->removeBookmark($userId, $this->id);
    }

    /**
     * @param  array<string, mixed>  $options
     */
    public function reply(string $text, array $options = []): Post
    {
        return $this->provider()->createPost($text, array_merge($options, [
            'reply' => ['in_reply_to_tweet_id' => $this->id],
        ]));
    }

    public function quote(string $text): Post
    {
        return $this->provider()->createPost($text, [
            'quote_tweet_id' => $this->id,
        ]);
    }

    public function hideReply(): void
    {
        $this->provider()->hideReply($this->id);
    }

    public function unhideReply(): void
    {
        $this->provider()->unhideReply($this->id);
    }

    public function delete(): void
    {
        $this->provider()->deletePost($this->id);
    }

    protected function provider(): XApiProvider
    {
        return $this->provider ?? throw new \LogicException('Post requires an XApiProvider. Call withProvider() first.');
    }
}
