<?php

namespace Dan\Shopify\Helpers;

use Dan\Shopify\Exceptions\GraphQLEnabledWithMissingQueriesException;

/**
 * Class Disputes.
 */
class Disputes extends Endpoint
{
    public function ensureGraphQLSupport(): void
    {
        if (config('shopify.endpoints.disputes')) {
            throw new GraphQLEnabledWithMissingQueriesException();
        }
    }
}
