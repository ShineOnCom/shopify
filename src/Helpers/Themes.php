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
        if (self::graphQLEnabled('themes')) {
            throw new GraphQLEnabledWithMissingQueriesException();
        }
    }
}
