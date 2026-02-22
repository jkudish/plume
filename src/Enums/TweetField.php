<?php

declare(strict_types=1);

namespace Plume\Enums;

enum TweetField: string
{
    case Attachments = 'attachments';
    case AuthorId = 'author_id';
    case ContextAnnotations = 'context_annotations';
    case ConversationId = 'conversation_id';
    case CreatedAt = 'created_at';
    case EditControls = 'edit_controls';
    case EditHistoryTweetIds = 'edit_history_tweet_ids';
    case Entities = 'entities';
    case Geo = 'geo';
    case Id = 'id';
    case InReplyToUserId = 'in_reply_to_user_id';
    case Lang = 'lang';
    case NonPublicMetrics = 'non_public_metrics';
    case OrganicMetrics = 'organic_metrics';
    case PossiblySensitive = 'possibly_sensitive';
    case PromotedMetrics = 'promoted_metrics';
    case PublicMetrics = 'public_metrics';
    case ReferencedTweets = 'referenced_tweets';
    case ReplySettings = 'reply_settings';
    case Source = 'source';
    case Text = 'text';
    case Withheld = 'withheld';
}
