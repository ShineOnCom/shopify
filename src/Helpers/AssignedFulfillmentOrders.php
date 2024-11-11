<?php

namespace Dan\Shopify\Helpers;

use Dan\Shopify\Exceptions\GraphQLEnabledWithMissingQueriesException;

class AssignedFulfillmentOrders extends Endpoint
{
    public function ensureGraphQLSupport(): void
    {
        if (self::graphQLEnabled('assigned_fulfillment_orders')) {
            throw new GraphQLEnabledWithMissingQueriesException();
        }
    }
}
