<?php

declare(strict_types=1);

namespace Plume\Data;

use Plume\Contracts\XApiProvider;

class XList
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly ?string $description = null,
        public readonly ?string $ownerId = null,
        public readonly bool $private = false,
        public readonly ?string $createdAt = null,
        public readonly ?ListMetrics $metrics = null,
        protected ?XApiProvider $provider = null,
    ) {}

    public function withProvider(XApiProvider $provider): static
    {
        $clone = clone $this;
        $clone->provider = $provider;

        return $clone;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(array $data): XList
    {
        return $this->provider()->updateList($this->id, $data);
    }

    public function delete(): void
    {
        $this->provider()->deleteList($this->id);
    }

    public function addMember(string $userId): void
    {
        $this->provider()->addListMember($this->id, $userId);
    }

    public function removeMember(string $userId): void
    {
        $this->provider()->removeListMember($this->id, $userId);
    }

    protected function provider(): XApiProvider
    {
        return $this->provider ?? throw new \LogicException('XList requires an XApiProvider. Call withProvider() first.');
    }
}
