<?php

namespace Dan\Shopify\Helpers;

use Dan\Shopify\Exceptions\GraphQLEnabledWithMissingQueriesException;

/**
 * Class Themes.
 *
 * @property \Dan\Shopify\Helpers\Assets assets
 */
class Themes extends Endpoint
{
    public function graphQLEnabled()
    {
        return parent::useGraphQL('themes');
    }

    public function ensureGraphQLSupport(): void
    {
        if ($this->graphQLEnabled()) {
            throw new GraphQLEnabledWithMissingQueriesException;
        }
    }
}
