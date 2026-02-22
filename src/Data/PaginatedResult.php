<?php

declare(strict_types=1);

namespace Plume\Data;

use Closure;

/**
 * @template T
 */
class PaginatedResult
{
    /**
     * @param  array<int, T>  $data
     */
    public function __construct(
        public readonly array $data,
        public readonly ?string $nextToken = null,
        public readonly ?string $previousToken = null,
        public readonly int $resultCount = 0,
        protected ?Closure $nextPageCallback = null,
    ) {}

    public function hasNextPage(): bool
    {
        return $this->nextToken !== null;
    }

    /**
     * @return self<T>|null
     */
    public function nextPage(): ?self
    {
        if (! $this->hasNextPage() || $this->nextPageCallback === null) {
            return null;
        }

        return ($this->nextPageCallback)($this->nextToken);
    }

    /**
     * @return self<T>
     */
    public function withNextPageCallback(Closure $callback): self
    {
        $clone = clone $this;
        $clone->nextPageCallback = $callback;

        return $clone;
    }
}
