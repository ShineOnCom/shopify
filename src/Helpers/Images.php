<?php

namespace Dan\Shopify\Helpers;

use Dan\Shopify\Exceptions\GraphQLEnabledWithMissingQueriesException;

/**
 * Class Images.
 */
class Images extends Endpoint
{
    public function ensureGraphQLSupport(): void
    {
        if (config('shopify.endpoints.images')) {
            throw new GraphQLEnabledWithMissingQueriesException();
        }
    }
}
