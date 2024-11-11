<?php

namespace Dan\Shopify\Helpers;

use Dan\Shopify\Exceptions\GraphQLEnabledWithMissingQueriesException;
use Dan\Shopify\Shopify;

class Metafields extends Endpoint
{
    public function ensureGraphQLSupport(): void
    {
        if (self::graphQLEnabled('metafields')) {
            throw new GraphQLEnabledWithMissingQueriesException();
        }
    }

}
