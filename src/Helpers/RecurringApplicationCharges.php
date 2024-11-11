<?php

namespace Dan\Shopify\Helpers;

use Dan\Shopify\Exceptions\GraphQLEnabledWithMissingQueriesException;

class RecurringApplicationCharges extends Endpoint
{
    public function ensureGraphQLSupport(): void
    {
        if (self::graphQLEnabled('recurring_application_charges')) {
            throw new GraphQLEnabledWithMissingQueriesException();
        }
    }
}
