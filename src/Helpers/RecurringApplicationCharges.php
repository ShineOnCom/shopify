<?php

namespace Dan\Shopify\Helpers;

use Dan\Shopify\Exceptions\GraphQLEnabledWithMissingQueriesException;

class RecurringApplicationCharges extends Endpoint
{
    public function graphQLEnabled()
    {
        return parent::useGraphQL('recurring_application_charges');
    }

    public function ensureGraphQLSupport(): void
    {
        if ($this->graphQLEnabled()) {
            throw new GraphQLEnabledWithMissingQueriesException;
        }
    }
}
