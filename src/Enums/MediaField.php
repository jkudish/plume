<?php

declare(strict_types=1);

namespace Plume\Enums;

enum MediaField: string
{
    case AltText = 'alt_text';
    case DurationMs = 'duration_ms';
    case Height = 'height';
    case MediaKey = 'media_key';
    case NonPublicMetrics = 'non_public_metrics';
    case OrganicMetrics = 'organic_metrics';
    case PreviewImageUrl = 'preview_image_url';
    case PromotedMetrics = 'promoted_metrics';
    case PublicMetrics = 'public_metrics';
    case Type = 'type';
    case Url = 'url';
    case Variants = 'variants';
    case Width = 'width';
}
