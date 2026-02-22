<?php

declare(strict_types=1);

namespace Plume\Data;

class Place
{
    public function __construct(
        public readonly string $id,
        public readonly string $fullName,
        public readonly ?string $name = null,
        public readonly ?string $country = null,
        public readonly ?string $countryCode = null,
        public readonly ?string $placeType = null,
        /** @var array<string, mixed> */
        public readonly array $geo = [],
    ) {}
}
