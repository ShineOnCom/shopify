<?php

namespace Dan\Shopify\Helpers;

use Dan\Shopify\Exceptions\GraphQLEnabledWithMissingQueriesException;

/**
 * Class Fulfillments.
 */
class Fulfillments extends Endpoint
{
    public function graphQLEnabled()
    {
        return parent::useGraphQL('fulfillments');
    }

    public function ensureGraphQLSupport(): void
    {
        if ($this->graphQLEnabled()) {
            throw new GraphQLEnabledWithMissingQueriesException();
        }
    }
}
