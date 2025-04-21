<?php

namespace Dan\Shopify\Helpers;

use Dan\Shopify\Exceptions\GraphQLEnabledWithMissingQueriesException;

class SmartCollections extends Endpoint
{
    public function graphQLEnabled()
    {
        return parent::useGraphQL('smart_collections');
    }

    public function ensureGraphQLSupport(): void
    {
        if ($this->graphQLEnabled()) {
            throw new GraphQLEnabledWithMissingQueriesException;
        }
    }
}
