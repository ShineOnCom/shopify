<?php

namespace Dan\Shopify\Helpers;

use Dan\Shopify\Exceptions\GraphQLEnabledWithMissingQueriesException;

/**
 * Class FulfillmentServices.
 */
class FulfillmentServices extends Endpoint
{
    public function ensureGraphQLSupport(): void
    {
        if (self::graphQLEnabled('fulfillment_services')) {
            throw new GraphQLEnabledWithMissingQueriesException();
        }
    }
}
