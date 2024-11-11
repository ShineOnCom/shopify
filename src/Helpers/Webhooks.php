<?php

namespace Dan\Shopify\Helpers;

use Dan\Shopify\Exceptions\GraphQLEnabledWithMissingQueriesException;

/**
 * Class Webhooks.
 */
class Webhooks extends Endpoint
{
    public function ensureGraphQLSupport(): void
    {
        if (self::graphQLEnabled('webhooks')) {
            throw new GraphQLEnabledWithMissingQueriesException();
        }
    }
}
