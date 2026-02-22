<?php

declare(strict_types=1);

namespace Plume;

use Plume\Concerns\ManagesBlocks;
use Plume\Concerns\ManagesBookmarks;
use Plume\Concerns\ManagesFollows;
use Plume\Concerns\ManagesLikes;
use Plume\Concerns\ManagesLists;
use Plume\Concerns\ManagesMedia;
use Plume\Concerns\ManagesMutes;
use Plume\Concerns\ManagesPosts;
use Plume\Concerns\ManagesRetweets;
use Plume\Concerns\ManagesSearch;
use Plume\Concerns\ManagesTimelines;
use Plume\Concerns\ManagesUsers;
use Plume\Concerns\MapsApiResponses;
use Plume\Contracts\HasXCredentials;
use Plume\Contracts\XApiProvider;
use Plume\Http\XHttpClient;

class XApiClient implements XApiProvider
{
    use ManagesBlocks;
    use ManagesBookmarks;
    use ManagesFollows;
    use ManagesLikes;
    use ManagesLists;
    use ManagesMedia;
    use ManagesMutes;
    use ManagesPosts;
    use ManagesRetweets;
    use ManagesSearch;
    use ManagesTimelines;
    use ManagesUsers;
    use MapsApiResponses;

    public function __construct(
        protected XHttpClient $http,
    ) {}

    /**
     * @param  HasXCredentials|array<string, string|null>  $credentials
     */
    public function forUser(HasXCredentials|array $credentials): ScopedXClient
    {
        if ($credentials instanceof HasXCredentials) {
            $credentials = $credentials->toXCredentials();
        }

        $accessToken = $credentials['access_token'] ?? null;
        // Treat empty string as null to avoid creating "Authorization: Bearer " header
        if ($accessToken === '') {
            $accessToken = null;
        }

        $userHttp = $this->http->withUserTokens(
            accessToken: $accessToken,
            refreshToken: $credentials['refresh_token'] ?? null,
            expiresAt: $credentials['expires_at'] ?? null,
        );

        $userClient = new self($userHttp);

        return new ScopedXClient($userClient);
    }
}
