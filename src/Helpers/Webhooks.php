<?php

namespace Dan\Shopify\Helpers;

use Dan\Shopify\Exceptions\GraphQLEnabledWithMissingQueriesException;

/**
 * Class Webhooks.
 */
class Webhooks extends Endpoint
{
    public function graphQLEnabled()
    {
        return parent::useGraphQL('webhooks');
    }

    public function ensureGraphQLSupport(): void
    {
        if ($this->graphQLEnabled()) {
            throw new GraphQLEnabledWithMissingQueriesException();
        }
    }
}
