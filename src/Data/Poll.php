<?php

declare(strict_types=1);

namespace Plume\Data;

class Poll
{
    /**
     * @param  array<int, array{position: int, label: string, votes: int}>  $options
     */
    public function __construct(
        public readonly string $id,
        /** @var array<int, array{position: int, label: string, votes: int}> */
        public readonly array $options = [],
        public readonly ?int $durationMinutes = null,
        public readonly ?string $endDatetime = null,
        public readonly ?string $votingStatus = null,
    ) {}
}
