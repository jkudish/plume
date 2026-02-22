<?php

declare(strict_types=1);

namespace Plume\Contracts;

interface HasXCredentials
{
    /**
     * @return array<string, string|null>
     */
    public function toXCredentials(): array;
}
