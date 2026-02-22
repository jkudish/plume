<?php

declare(strict_types=1);

namespace Plume\Data;

class UserMetrics
{
    public function __construct(
        public readonly int $followersCount = 0,
        public readonly int $followingCount = 0,
        public readonly int $tweetCount = 0,
        public readonly int $listedCount = 0,
    ) {}
}
