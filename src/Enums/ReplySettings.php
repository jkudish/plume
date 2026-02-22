<?php

declare(strict_types=1);

namespace Plume\Enums;

enum ReplySettings: string
{
    case Everyone = 'everyone';
    case Following = 'following';
    case MentionedUsers = 'mentionedUsers';
    case Subscribers = 'subscribers';
    case Verified = 'verified';
}
