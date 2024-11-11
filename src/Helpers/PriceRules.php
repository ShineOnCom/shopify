<?php

namespace Dan\Shopify\Helpers;

use Dan\Shopify\Exceptions\GraphQLEnabledWithMissingQueriesException;

class PriceRules extends Endpoint
{
    public function ensureGraphQLSupport(): void
    {
        if (self::graphQLEnabled('price_rules')) {
            throw new GraphQLEnabledWithMissingQueriesException();
        }
    }
}
