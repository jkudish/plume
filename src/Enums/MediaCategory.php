<?php

declare(strict_types=1);

namespace Plume\Enums;

enum MediaCategory: string
{
    case TweetImage = 'tweet_image';
    case TweetGif = 'tweet_gif';
    case TweetVideo = 'tweet_video';
    case AmplifyVideo = 'amplify_video';
    case Subtitles = 'subtitles';
}
