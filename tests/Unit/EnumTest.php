<?php

declare(strict_types=1);

use Plume\Enums\Exclude;
use Plume\Enums\Expansion;
use Plume\Enums\Granularity;
use Plume\Enums\ListField;
use Plume\Enums\MediaCategory;
use Plume\Enums\MediaField;
use Plume\Enums\MediaType;
use Plume\Enums\PlaceField;
use Plume\Enums\PollField;
use Plume\Enums\ReplySettings;
use Plume\Enums\SortOrder;
use Plume\Enums\TweetField;
use Plume\Enums\UserField;

it('has correct tweet field values', function (): void {
    expect(TweetField::AuthorId->value)->toBe('author_id')
        ->and(TweetField::CreatedAt->value)->toBe('created_at')
        ->and(TweetField::PublicMetrics->value)->toBe('public_metrics')
        ->and(TweetField::ReferencedTweets->value)->toBe('referenced_tweets')
        ->and(TweetField::ReplySettings->value)->toBe('reply_settings');
});

it('has correct user field values', function (): void {
    expect(UserField::Username->value)->toBe('username')
        ->and(UserField::ProfileImageUrl->value)->toBe('profile_image_url')
        ->and(UserField::PublicMetrics->value)->toBe('public_metrics')
        ->and(UserField::VerifiedType->value)->toBe('verified_type');
});

it('has correct media field values', function (): void {
    expect(MediaField::MediaKey->value)->toBe('media_key')
        ->and(MediaField::DurationMs->value)->toBe('duration_ms')
        ->and(MediaField::PreviewImageUrl->value)->toBe('preview_image_url');
});

it('has correct expansion values', function (): void {
    expect(Expansion::AuthorId->value)->toBe('author_id')
        ->and(Expansion::ReferencedTweetsId->value)->toBe('referenced_tweets.id')
        ->and(Expansion::AttachmentsMediaKeys->value)->toBe('attachments.media_keys')
        ->and(Expansion::AttachmentsPollIds->value)->toBe('attachments.poll_ids')
        ->and(Expansion::GeoPlaceId->value)->toBe('geo.place_id');
});

it('has correct reply settings values', function (): void {
    expect(ReplySettings::Everyone->value)->toBe('everyone')
        ->and(ReplySettings::Following->value)->toBe('following')
        ->and(ReplySettings::MentionedUsers->value)->toBe('mentionedUsers');
});

it('has correct media category values', function (): void {
    expect(MediaCategory::TweetImage->value)->toBe('tweet_image')
        ->and(MediaCategory::TweetVideo->value)->toBe('tweet_video')
        ->and(MediaCategory::TweetGif->value)->toBe('tweet_gif');
});

it('has correct media type values', function (): void {
    expect(MediaType::Photo->value)->toBe('photo')
        ->and(MediaType::Video->value)->toBe('video')
        ->and(MediaType::AnimatedGif->value)->toBe('animated_gif');
});

it('has correct sort order values', function (): void {
    expect(SortOrder::Recency->value)->toBe('recency')
        ->and(SortOrder::Relevancy->value)->toBe('relevancy');
});

it('has correct exclude values', function (): void {
    expect(Exclude::Replies->value)->toBe('replies')
        ->and(Exclude::Retweets->value)->toBe('retweets');
});

it('has correct granularity values', function (): void {
    expect(Granularity::Minute->value)->toBe('minute')
        ->and(Granularity::Hour->value)->toBe('hour')
        ->and(Granularity::Day->value)->toBe('day');
});

it('has correct poll field values', function (): void {
    expect(PollField::DurationMinutes->value)->toBe('duration_minutes')
        ->and(PollField::EndDatetime->value)->toBe('end_datetime')
        ->and(PollField::VotingStatus->value)->toBe('voting_status');
});

it('has correct place field values', function (): void {
    expect(PlaceField::FullName->value)->toBe('full_name')
        ->and(PlaceField::CountryCode->value)->toBe('country_code')
        ->and(PlaceField::PlaceType->value)->toBe('place_type');
});

it('has correct list field values', function (): void {
    expect(ListField::FollowerCount->value)->toBe('follower_count')
        ->and(ListField::MemberCount->value)->toBe('member_count')
        ->and(ListField::OwnerId->value)->toBe('owner_id');
});
