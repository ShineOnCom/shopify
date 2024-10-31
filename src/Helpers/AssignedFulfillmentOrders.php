<?php

namespace Dan\Shopify\Helpers;

use Dan\Shopify\Exceptions\GraphQLEnabledWithMissingQueriesException;

class AssignedFulfillmentOrders extends Endpoint
{
    protected function ensureGraphQLSupport(): void
	{
		if (config('shopify.endpoints.assigned_fulfillment_orders')) {
            throw new GraphQLEnabledWithMissingQueriesException(self::GRAPHQL_NOT_SUPPORTED_YET_ERROR);
        }
	}
}
