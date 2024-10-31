<?php

namespace Dan\Shopify\Helpers;

use Dan\Shopify\Exceptions\GraphQLEnabledWithMissingQueriesException;

/**
 * Class Themes.
 *
 * @property \Dan\Shopify\Helpers\Assets assets
 */
class Themes extends Endpoint
{
    public function ensureGraphQLSupport(): void
    {
        if (config('shopify.endpoints.themes')) {
            throw new GraphQLEnabledWithMissingQueriesException(self::GRAPHQL_NOT_SUPPORTED_YET_ERROR);
        }
    }
}
