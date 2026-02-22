<?php

declare(strict_types=1);

namespace Plume\Data;

class Includes
{
    /**
     * @param  array<int, User>  $users
     * @param  array<int, Post>  $tweets
     * @param  array<int, Media>  $media
     * @param  array<int, Poll>  $polls
     * @param  array<int, Place>  $places
     */
    public function __construct(
        public readonly array $users = [],
        public readonly array $tweets = [],
        public readonly array $media = [],
        public readonly array $polls = [],
        public readonly array $places = [],
    ) {}
}
