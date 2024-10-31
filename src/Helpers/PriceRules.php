<?php

namespace Dan\Shopify\Helpers;

use Dan\Shopify\Exceptions\GraphQLEnabledWithMissingQueriesException;

class PriceRules extends Endpoint
{
    public function ensureGraphQLSupport(): void
    {
        if (config('shopify.endpoints.price_rules')) {
            throw new GraphQLEnabledWithMissingQueriesException();
        }
    }
}
