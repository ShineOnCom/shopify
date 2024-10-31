<?php

namespace Dan\Shopify\Helpers;

use Dan\Shopify\Exceptions\GraphQLEnabledWithMissingQueriesException;

class DiscountCodes extends Endpoint
{
    public function ensureGraphQLSupport(): void
    {
        if (config('shopify.endpoints.discount_codes')) {
            throw new GraphQLEnabledWithMissingQueriesException();
        }
    }
}
