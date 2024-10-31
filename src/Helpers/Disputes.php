<?php

namespace Dan\Shopify\Helpers;

use Dan\Shopify\Exceptions\GraphQLEnabledWithMissingQueriesException;

/**
 * Class Disputes.
 */
class Disputes extends Endpoint
{
	protected function ensureGraphQLSupport(): void
	{
		if (config('shopify.endpoints.disputes')) {
            throw new GraphQLEnabledWithMissingQueriesException(self::GRAPHQL_NOT_SUPPORTED_YET_ERROR);
        }
	}
}
