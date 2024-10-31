<?php

namespace Dan\Shopify\Helpers;

use Dan\Shopify\Exceptions\GraphQLEnabledWithMissingQueriesException;

/**
 * Class FulfillmentServices.
 */
class FulfillmentServices extends Endpoint
{
	public function ensureGraphQLSupport(): void
	{
		if (config('shopify.endpoints.fulfillment_services')) {
            throw new GraphQLEnabledWithMissingQueriesException(self::GRAPHQL_NOT_SUPPORTED_YET_ERROR);
        }
	}
}
