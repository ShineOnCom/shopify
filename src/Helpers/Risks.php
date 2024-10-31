<?php

namespace Dan\Shopify\Helpers;

use Dan\Shopify\Exceptions\GraphQLEnabledWithMissingQueriesException;

/**
 * Class Risks.
 */
class Risks extends Endpoint
{
	public function ensureGraphQLSupport(): void
	{
		if (config('shopify.endpoints.risks')) {
            throw new GraphQLEnabledWithMissingQueriesException(self::GRAPHQL_NOT_SUPPORTED_YET_ERROR);
        }
	}
}
