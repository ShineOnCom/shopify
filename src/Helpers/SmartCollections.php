<?php

namespace Dan\Shopify\Helpers;

use Dan\Shopify\Exceptions\GraphQLEnabledWithMissingQueriesException;

class SmartCollections extends Endpoint
{
	protected function ensureGraphQLSupport(): void
	{
		if (config('shopify.endpoints.smart_collections')) {
            throw new GraphQLEnabledWithMissingQueriesException(self::GRAPHQL_NOT_SUPPORTED_YET_ERROR);
        }
	}
}
