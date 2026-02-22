<?php

declare(strict_types=1);

namespace Plume\Data;

class PostMetrics
{
    public function __construct(
        public readonly int $retweetCount = 0,
        public readonly int $replyCount = 0,
        public readonly int $likeCount = 0,
        public readonly int $quoteCount = 0,
        public readonly int $bookmarkCount = 0,
        public readonly int $impressionCount = 0,
    ) {}
}
