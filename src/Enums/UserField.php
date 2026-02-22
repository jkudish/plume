<?php

declare(strict_types=1);

namespace Plume\Enums;

enum UserField: string
{
    case Affiliation = 'affiliation';
    case ConnectionStatus = 'connection_status';
    case CreatedAt = 'created_at';
    case Description = 'description';
    case Entities = 'entities';
    case Id = 'id';
    case Location = 'location';
    case MostRecentTweetId = 'most_recent_tweet_id';
    case Name = 'name';
    case PinnedTweetId = 'pinned_tweet_id';
    case ProfileBannerUrl = 'profile_banner_url';
    case ProfileImageUrl = 'profile_image_url';
    case Protected = 'protected';
    case PublicMetrics = 'public_metrics';
    case ReceivesYourDm = 'receives_your_dm';
    case SubscriptionType = 'subscription_type';
    case Url = 'url';
    case Username = 'username';
    case Verified = 'verified';
    case VerifiedType = 'verified_type';
    case Withheld = 'withheld';
}
