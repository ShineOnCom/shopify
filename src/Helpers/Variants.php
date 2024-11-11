<?php

namespace Dan\Shopify\Helpers;

use Dan\Shopify\Exceptions\GraphQLEnabledWithMissingQueriesException;

/**
 * Class Variants.
 */
class Variants extends Endpoint
{
    public function ensureGraphQLSupport(): void
    {
        if (self::graphQLEnabled('variants')) {
            throw new GraphQLEnabledWithMissingQueriesException();
        }
    }
}
