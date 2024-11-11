<?php

namespace Dan\Shopify\Helpers;

use Dan\Shopify\Exceptions\GraphQLEnabledWithMissingQueriesException;

/**
 * Class Products.
 */
class Products extends Endpoint
{
    public function ensureGraphQLSupport(): void
    {
        if (self::graphQLEnabled('products')) {
            throw new GraphQLEnabledWithMissingQueriesException();
        }
    }
}
