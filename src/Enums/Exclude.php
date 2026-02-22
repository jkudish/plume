<?php

declare(strict_types=1);

namespace Plume\Enums;

enum Exclude: string
{
    case Replies = 'replies';
    case Retweets = 'retweets';
}
