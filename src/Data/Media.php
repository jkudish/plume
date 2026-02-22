<?php

declare(strict_types=1);

namespace Plume\Data;

class Media
{
    public function __construct(
        public readonly string $mediaKey,
        public readonly string $type,
        public readonly ?string $url = null,
        public readonly ?string $previewImageUrl = null,
        public readonly ?string $altText = null,
        public readonly ?int $height = null,
        public readonly ?int $width = null,
        public readonly ?int $durationMs = null,
        /** @var array<int, array<string, mixed>> */
        public readonly array $variants = [],
    ) {}
}
