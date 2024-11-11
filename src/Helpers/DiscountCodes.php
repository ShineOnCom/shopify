<?php

namespace Dan\Shopify\Helpers;

use Dan\Shopify\Exceptions\GraphQLEnabledWithMissingQueriesException;

class DiscountCodes extends Endpoint
{
    public function ensureGraphQLSupport(): void
    {
        if (self::graphQLEnabled('discount_codes')) {
            throw new GraphQLEnabledWithMissingQueriesException();
        }
    }
}
