<?php

declare(strict_types=1);

namespace Plume\Enums;

enum PlaceField: string
{
    case ContainedWithin = 'contained_within';
    case Country = 'country';
    case CountryCode = 'country_code';
    case FullName = 'full_name';
    case Geo = 'geo';
    case Id = 'id';
    case Name = 'name';
    case PlaceType = 'place_type';
}
