<?php

namespace Dan\Shopify\Helpers;

use Dan\Shopify\Exceptions\GraphQLEnabledWithMissingQueriesException;

class Metafields extends Endpoint
{
    public function graphQLEnabled()
    {
        return parent::useGraphQL('metafields');
    }

    public function ensureGraphQLSupport(): void
    {
        if ($this->graphQLEnabled()) {
            throw new GraphQLEnabledWithMissingQueriesException;
        }
    }
}
