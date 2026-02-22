<?php

declare(strict_types=1);

namespace Plume\Enums;

enum ListField: string
{
    case CreatedAt = 'created_at';
    case Description = 'description';
    case FollowerCount = 'follower_count';
    case Id = 'id';
    case MemberCount = 'member_count';
    case Name = 'name';
    case OwnerId = 'owner_id';
    case Private = 'private';
}
