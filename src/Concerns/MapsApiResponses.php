<?php

declare(strict_types=1);

namespace Plume\Concerns;

use Plume\Data\Includes;
use Plume\Data\ListMetrics;
use Plume\Data\Media;
use Plume\Data\PaginatedResult;
use Plume\Data\Place;
use Plume\Data\Poll;
use Plume\Data\Post;
use Plume\Data\PostMetrics;
use Plume\Data\User;
use Plume\Data\UserMetrics;
use Plume\Data\XList;
use Plume\Enums\Expansion;
use Plume\Enums\ListField;
use Plume\Enums\MediaField;
use Plume\Enums\PlaceField;
use Plume\Enums\PollField;
use Plume\Enums\TweetField;
use Plume\Enums\UserField;

trait MapsApiResponses
{
    /**
     * @param  array<string, mixed>  $data
     * @param  array<string, mixed>  $response
     */
    protected function mapPost(array $data, array $response = []): Post
    {
        $metrics = isset($data['public_metrics']) ? new PostMetrics(
            retweetCount: $data['public_metrics']['retweet_count'] ?? 0,
            replyCount: $data['public_metrics']['reply_count'] ?? 0,
            likeCount: $data['public_metrics']['like_count'] ?? 0,
            quoteCount: $data['public_metrics']['quote_count'] ?? 0,
            bookmarkCount: $data['public_metrics']['bookmark_count'] ?? 0,
            impressionCount: $data['public_metrics']['impression_count'] ?? 0,
        ) : null;

        $includes = isset($response['includes']) ? $this->mapIncludes($response['includes']) : null;

        return (new Post(
            id: $data['id'],
            text: $data['text'] ?? '',
            authorId: $data['author_id'] ?? null,
            conversationId: $data['conversation_id'] ?? null,
            inReplyToUserId: $data['in_reply_to_user_id'] ?? null,
            createdAt: $data['created_at'] ?? null,
            lang: $data['lang'] ?? null,
            source: $data['source'] ?? null,
            replySettings: $data['reply_settings'] ?? null,
            possiblySensitive: $data['possibly_sensitive'] ?? false,
            publicMetrics: $metrics,
            referencedTweets: $data['referenced_tweets'] ?? [],
            entities: $data['entities'] ?? [],
            attachments: $data['attachments'] ?? [],
            includes: $includes,
        ))->withProvider($this);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function mapUser(array $data): User
    {
        $metrics = isset($data['public_metrics']) ? new UserMetrics(
            followersCount: $data['public_metrics']['followers_count'] ?? 0,
            followingCount: $data['public_metrics']['following_count'] ?? 0,
            tweetCount: $data['public_metrics']['tweet_count'] ?? 0,
            listedCount: $data['public_metrics']['listed_count'] ?? 0,
        ) : null;

        return (new User(
            id: $data['id'],
            name: $data['name'],
            username: $data['username'],
            description: $data['description'] ?? null,
            location: $data['location'] ?? null,
            url: $data['url'] ?? null,
            profileImageUrl: $data['profile_image_url'] ?? null,
            profileBannerUrl: $data['profile_banner_url'] ?? null,
            createdAt: $data['created_at'] ?? null,
            pinnedTweetId: $data['pinned_tweet_id'] ?? null,
            protected: $data['protected'] ?? false,
            verified: $data['verified'] ?? false,
            verifiedType: $data['verified_type'] ?? null,
            publicMetrics: $metrics,
            entities: $data['entities'] ?? [],
        ))->withProvider($this);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function mapList(array $data): XList
    {
        $metrics = null;
        if (isset($data['follower_count']) || isset($data['member_count'])) {
            $metrics = new ListMetrics(
                followerCount: $data['follower_count'] ?? 0,
                memberCount: $data['member_count'] ?? 0,
            );
        }

        return (new XList(
            id: $data['id'],
            name: $data['name'],
            description: $data['description'] ?? null,
            ownerId: $data['owner_id'] ?? null,
            private: $data['private'] ?? false,
            createdAt: $data['created_at'] ?? null,
            metrics: $metrics,
        ))->withProvider($this);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function mapMedia(array $data): Media
    {
        return new Media(
            mediaKey: $data['media_key'],
            type: $data['type'],
            url: $data['url'] ?? null,
            previewImageUrl: $data['preview_image_url'] ?? null,
            altText: $data['alt_text'] ?? null,
            height: $data['height'] ?? null,
            width: $data['width'] ?? null,
            durationMs: $data['duration_ms'] ?? null,
            variants: $data['variants'] ?? [],
        );
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function mapPoll(array $data): Poll
    {
        return new Poll(
            id: $data['id'],
            options: $data['options'] ?? [],
            durationMinutes: $data['duration_minutes'] ?? null,
            endDatetime: $data['end_datetime'] ?? null,
            votingStatus: $data['voting_status'] ?? null,
        );
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function mapPlace(array $data): Place
    {
        return new Place(
            id: $data['id'],
            fullName: $data['full_name'],
            name: $data['name'] ?? null,
            country: $data['country'] ?? null,
            countryCode: $data['country_code'] ?? null,
            placeType: $data['place_type'] ?? null,
            geo: $data['geo'] ?? [],
        );
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function mapIncludes(array $data): Includes
    {
        $users = array_map(fn (array $u): User => $this->mapUser($u), $data['users'] ?? []);
        $tweets = array_map(fn (array $t): Post => $this->mapPost($t), $data['tweets'] ?? []);
        $media = array_map(fn (array $m): Media => $this->mapMedia($m), $data['media'] ?? []);
        $polls = array_map(fn (array $p): Poll => $this->mapPoll($p), $data['polls'] ?? []);
        $places = array_map(fn (array $p): Place => $this->mapPlace($p), $data['places'] ?? []);

        return new Includes(
            users: array_values($users),
            tweets: array_values($tweets),
            media: array_values($media),
            polls: array_values($polls),
            places: array_values($places),
        );
    }

    /**
     * @param  list<TweetField>  $tweetFields
     * @param  list<Expansion>  $expansions
     * @param  list<UserField>  $userFields
     * @param  list<MediaField>  $mediaFields
     * @param  list<PollField>  $pollFields
     * @param  list<PlaceField>  $placeFields
     * @param  list<ListField>  $listFields
     * @return array<string, string>
     */
    protected function buildFieldQuery(
        array $tweetFields = [],
        array $expansions = [],
        array $userFields = [],
        array $mediaFields = [],
        array $pollFields = [],
        array $placeFields = [],
        array $listFields = [],
    ): array {
        $query = [];

        if ($tweetFields !== []) {
            $query['tweet.fields'] = implode(',', array_map(fn (TweetField $f): string => $f->value, $tweetFields));
        }

        if ($expansions !== []) {
            $query['expansions'] = implode(',', array_map(fn (Expansion $e): string => $e->value, $expansions));
        }

        if ($userFields !== []) {
            $query['user.fields'] = implode(',', array_map(fn (UserField $f): string => $f->value, $userFields));
        }

        if ($mediaFields !== []) {
            $query['media.fields'] = implode(',', array_map(fn (MediaField $f): string => $f->value, $mediaFields));
        }

        if ($pollFields !== []) {
            $query['poll.fields'] = implode(',', array_map(fn (PollField $f): string => $f->value, $pollFields));
        }

        if ($placeFields !== []) {
            $query['place.fields'] = implode(',', array_map(fn (PlaceField $f): string => $f->value, $placeFields));
        }

        if ($listFields !== []) {
            $query['list.fields'] = implode(',', array_map(fn (ListField $f): string => $f->value, $listFields));
        }

        return $query;
    }

    /**
     * @param  array<string, mixed>  $response
     * @return PaginatedResult<Post>
     */
    protected function paginatedPosts(array $response, ?\Closure $nextPageCallback = null): PaginatedResult
    {
        /** @var array<int, array<string, mixed>> $dataItems */
        $dataItems = $response['data'] ?? [];
        $posts = array_map(fn (array $item): Post => $this->mapPost($item, $response), $dataItems);

        $result = new PaginatedResult(
            data: array_values($posts),
            nextToken: $response['meta']['next_token'] ?? null,
            previousToken: $response['meta']['previous_token'] ?? null,
            resultCount: $response['meta']['result_count'] ?? count($posts),
        );

        if ($nextPageCallback !== null) {
            $result = $result->withNextPageCallback($nextPageCallback);
        }

        return $result;
    }

    /**
     * @param  array<string, mixed>  $response
     * @return PaginatedResult<User>
     */
    protected function paginatedUsers(array $response, ?\Closure $nextPageCallback = null): PaginatedResult
    {
        /** @var array<int, array<string, mixed>> $dataItems */
        $dataItems = $response['data'] ?? [];
        $users = array_map(fn (array $item): User => $this->mapUser($item), $dataItems);

        $result = new PaginatedResult(
            data: array_values($users),
            nextToken: $response['meta']['next_token'] ?? null,
            previousToken: $response['meta']['previous_token'] ?? null,
            resultCount: $response['meta']['result_count'] ?? count($users),
        );

        if ($nextPageCallback !== null) {
            $result = $result->withNextPageCallback($nextPageCallback);
        }

        return $result;
    }

    /**
     * @param  array<string, mixed>  $response
     * @return PaginatedResult<XList>
     */
    protected function paginatedLists(array $response, ?\Closure $nextPageCallback = null): PaginatedResult
    {
        /** @var array<int, array<string, mixed>> $dataItems */
        $dataItems = $response['data'] ?? [];
        $lists = array_map(fn (array $item): XList => $this->mapList($item), $dataItems);

        $result = new PaginatedResult(
            data: array_values($lists),
            nextToken: $response['meta']['next_token'] ?? null,
            previousToken: $response['meta']['previous_token'] ?? null,
            resultCount: $response['meta']['result_count'] ?? count($lists),
        );

        if ($nextPageCallback !== null) {
            $result = $result->withNextPageCallback($nextPageCallback);
        }

        return $result;
    }
}
