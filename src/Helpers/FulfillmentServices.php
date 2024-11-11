<?php

namespace Dan\Shopify\Helpers;

use Dan\Shopify\Exceptions\GraphQLEnabledWithMissingQueriesException;

/**
 * Class FulfillmentServices.
 */
class FulfillmentServices extends Endpoint
{
    public function graphQLEnabled()
    {
        return parent::useGraphQL('fulfillment_services');
    }

    public function ensureGraphQLSupport(): void
    {
        if ($this->graphQLEnabled()) {
            throw new GraphQLEnabledWithMissingQueriesException();
        }
    }
}
