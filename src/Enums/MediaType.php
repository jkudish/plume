<?php

declare(strict_types=1);

namespace Plume\Enums;

enum MediaType: string
{
    case Photo = 'photo';
    case Video = 'video';
    case AnimatedGif = 'animated_gif';
}
