<?php

declare(strict_types=1);

namespace Plume\Concerns;

use Plume\Data\Post;
use Plume\Enums\Expansion;
use Plume\Enums\MediaField;
use Plume\Enums\PlaceField;
use Plume\Enums\PollField;
use Plume\Enums\TweetField;
use Plume\Enums\UserField;

trait ManagesPosts
{
    /**
     * @param  array<string, mixed>  $options
     */
    public function createPost(string $text, array $options = []): Post
    {
        $response = $this->http->post('/2/tweets', array_merge(['text' => $text], $options));

        /** @var array{id: string, text: string} $data */
        $data = $response['data'];

        return $this->mapPost($data, $response);
    }

    public function deletePost(string $id): void
    {
        $this->http->delete("/2/tweets/{$id}");
    }

    /**
     * @param  list<TweetField>  $tweetFields
     * @param  list<Expansion>  $expansions
     * @param  list<UserField>  $userFields
     * @param  list<MediaField>  $mediaFields
     * @param  list<PollField>  $pollFields
     * @param  list<PlaceField>  $placeFields
     */
    public function getPost(
        string $id,
        array $tweetFields = [],
        array $expansions = [],
        array $userFields = [],
        array $mediaFields = [],
        array $pollFields = [],
        array $placeFields = [],
    ): Post {
        $query = $this->buildFieldQuery($tweetFields, $expansions, $userFields, $mediaFields, $pollFields, $placeFields);
        $response = $this->http->get("/2/tweets/{$id}", $query);

        /** @var array<string, mixed> $data */
        $data = $response['data'];

        return $this->mapPost($data, $response);
    }

    /**
     * @param  list<string>  $ids
     * @param  list<TweetField>  $tweetFields
     * @param  list<Expansion>  $expansions
     * @param  list<UserField>  $userFields
     * @param  list<MediaField>  $mediaFields
     * @param  list<PollField>  $pollFields
     * @param  list<PlaceField>  $placeFields
     * @return list<Post>
     */
    public function getPosts(
        array $ids,
        array $tweetFields = [],
        array $expansions = [],
        array $userFields = [],
        array $mediaFields = [],
        array $pollFields = [],
        array $placeFields = [],
    ): array {
        $query = array_merge(
            ['ids' => implode(',', $ids)],
            $this->buildFieldQuery($tweetFields, $expansions, $userFields, $mediaFields, $pollFields, $placeFields),
        );
        $response = $this->http->get('/2/tweets', $query);

        /** @var array<int, array<string, mixed>> $dataItems */
        $dataItems = $response['data'] ?? [];

        return array_values(array_map(fn (array $item): Post => $this->mapPost($item, $response), $dataItems));
    }

    public function hideReply(string $id): void
    {
        $this->http->put("/2/tweets/{$id}/hidden", ['hidden' => true]);
    }

    public function unhideReply(string $id): void
    {
        $this->http->put("/2/tweets/{$id}/hidden", ['hidden' => false]);
    }
}
