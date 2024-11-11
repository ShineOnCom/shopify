<?php

namespace Dan\Shopify\Helpers;

use Dan\Shopify\Exceptions\GraphQLEnabledWithMissingQueriesException;

class PriceRules extends Endpoint
{
    public function graphQLEnabled()
    {
        return parent::useGraphQL('price_rules');
    }

    public function ensureGraphQLSupport(): void
    {
        if ($this->graphQLEnabled()) {
            throw new GraphQLEnabledWithMissingQueriesException();
        }
    }
}
