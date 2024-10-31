<?php

namespace Dan\Shopify\Helpers;

use Dan\Shopify\Exceptions\GraphQLEnabledWithMissingQueriesException;

/**
 * Class Products.
 */
class Products extends Endpoint
{
	public function ensureGraphQLSupport(): void
	{
		if (config('shopify.endpoints.products')) {
            throw new GraphQLEnabledWithMissingQueriesException(self::GRAPHQL_NOT_SUPPORTED_YET_ERROR);
        }
	}
}
