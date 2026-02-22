<?php

declare(strict_types=1);

namespace Plume\Enums;

enum SortOrder: string
{
    case Recency = 'recency';
    case Relevancy = 'relevancy';
}
