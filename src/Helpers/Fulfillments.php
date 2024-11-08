<?php

namespace Dan\Shopify\Helpers;

use Dan\Shopify\Exceptions\GraphQLEnabledWithMissingQueriesException;

/**
 * Class Fulfillments.
 */
class Fulfillments extends Endpoint
{
    public function ensureGraphQLSupport(): void
    {
        if (config('shopify.endpoints.fulfillments')) {
            throw new GraphQLEnabledWithMissingQueriesException();
        }
    }
}
