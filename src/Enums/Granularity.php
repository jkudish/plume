<?php

declare(strict_types=1);

namespace Plume\Enums;

enum Granularity: string
{
    case Minute = 'minute';
    case Hour = 'hour';
    case Day = 'day';
}
