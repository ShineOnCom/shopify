<?php

namespace Dan\Shopify\Models;

use Dan\Shopify\Util;
use Illuminate\Support\Arr;

/**
 * Class Publication.
 *
 * @property int $id
 * @property string $name
 */
class Publication extends AbstractModel
{
    /** @var string */
    public static $resource_name = 'publication';

    /** @var string */
    public static $resource_name_many = 'publications';

    public function transformGraphQLResponse(array $response): ?array
    {
        $response = Util::convertKeysToSnakeCase($response);
        if ($publications = Arr::get($response, 'data.publications')) {
            return $publications;
        }

        return Arr::get($response, 'data.publication') ?? $response;
    }
}
