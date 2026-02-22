<?php

declare(strict_types=1);

namespace Plume\Enums;

enum PollField: string
{
    case DurationMinutes = 'duration_minutes';
    case EndDatetime = 'end_datetime';
    case Id = 'id';
    case Options = 'options';
    case VotingStatus = 'voting_status';
}
