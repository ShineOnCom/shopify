<?php

namespace Dan\Shopify\Helpers;

use Dan\Shopify\Exceptions\GraphQLEnabledWithMissingQueriesException;

/**
 * Class Orders.
 */
class Orders extends Endpoint
{
    public function graphQLEnabled()
    {
        return parent::useGraphQL('orders');
    }

    public function ensureGraphQLSupport(): void
    {
        if ($this->graphQLEnabled()) {
            throw new GraphQLEnabledWithMissingQueriesException();
        }
    }
}
