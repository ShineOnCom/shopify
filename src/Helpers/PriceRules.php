<?php

namespace Dan\Shopify\Helpers;

use Dan\Shopify\Exceptions\GraphQLEnabledWithMissingQueriesException;

class PriceRules extends Endpoint
{
	protected function ensureGraphQLSupport(): void
	{
		if (config('shopify.endpoints.price_rules')) {
            throw new GraphQLEnabledWithMissingQueriesException(self::GRAPHQL_NOT_SUPPORTED_YET_ERROR);
        }
	}
}
