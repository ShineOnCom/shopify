<?php

namespace Dan\Shopify\Helpers;

use Dan\Shopify\Exceptions\GraphQLEnabledWithMissingQueriesException;

class AssignedFulfillmentOrders extends Endpoint
{

    public function graphQLEnabled()
    {
        return parent::useGraphQL('assigned_fulfillment_orders');
    }
    public function ensureGraphQLSupport(): void
    {
        if ($this->graphQLEnabled()) {
            throw new GraphQLEnabledWithMissingQueriesException();
        }
    }
}
