<?php

namespace Dan\Shopify\Helpers;

use Dan\Shopify\Exceptions\GraphQLEnabledWithMissingQueriesException;

class DiscountCodes extends Endpoint
{
    public function graphQLEnabled()
    {
        return parent::useGraphQL('discount_codes');
    }

    public function ensureGraphQLSupport(): void
    {
        if ($this->graphQLEnabled()) {
            throw new GraphQLEnabledWithMissingQueriesException;
        }
    }
}
