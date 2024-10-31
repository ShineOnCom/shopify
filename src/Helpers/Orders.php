<?php

namespace Dan\Shopify\Helpers;

use Dan\Shopify\Exceptions\GraphQLEnabledWithMissingQueriesException;

/**
 * Class Orders.
 */
class Orders extends Endpoint
{
    public function ensureGraphQLSupport(): void
    {
        if (config('shopify.endpoints.orders')) {
            throw new GraphQLEnabledWithMissingQueriesException();
        }
    }
}
