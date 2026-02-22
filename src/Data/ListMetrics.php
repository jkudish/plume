<?php

declare(strict_types=1);

namespace Plume\Data;

class ListMetrics
{
    public function __construct(
        public readonly int $followerCount = 0,
        public readonly int $memberCount = 0,
    ) {}
}
