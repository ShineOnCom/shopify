<?php

namespace Dan\Shopify\Helpers;

use Dan\Shopify\Exceptions\GraphQLEnabledWithMissingQueriesException;

class SmartCollections extends Endpoint
{
    public function ensureGraphQLSupport(): void
    {
        if (self::graphQLEnabled('smart_collections')) {
            throw new GraphQLEnabledWithMissingQueriesException();
        }
    }
}
