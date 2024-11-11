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
        if (self::graphQLEnabled('disputes')) {
            throw new GraphQLEnabledWithMissingQueriesException();
        }
    }
}
