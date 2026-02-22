<?php

declare(strict_types=1);

namespace Plume\Enums;

enum Expansion: string
{
    case AuthorId = 'author_id';
    case ReferencedTweetsId = 'referenced_tweets.id';
    case ReferencedTweetsIdAuthorId = 'referenced_tweets.id.author_id';
    case InReplyToUserId = 'in_reply_to_user_id';
    case AttachmentsMediaKeys = 'attachments.media_keys';
    case AttachmentsPollIds = 'attachments.poll_ids';
    case GeoPlaceId = 'geo.place_id';
    case EntitiesMentionsUsername = 'entities.mentions.username';
    case EditHistoryTweetIds = 'edit_history_tweet_ids';
    case MostRecentTweetId = 'most_recent_tweet_id';
    case PinnedTweetId = 'pinned_tweet_id';
}
