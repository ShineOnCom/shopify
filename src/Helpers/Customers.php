<?php

namespace Dan\Shopify\Helpers;

use Dan\Shopify\Exceptions\GraphQLEnabledWithMissingQueriesException;
use Dan\Shopify\Exceptions\InvalidOrMissingEndpointException;

/**
 * Class Customers.
 */
class Customers extends Endpoint
{
    public function ensureGraphQLSupport(): void
	{
		if (config('shopify.endpoints.customers')) {
            throw new GraphQLEnabledWithMissingQueriesException(self::GRAPHQL_NOT_SUPPORTED_YET_ERROR);
        }
	}

    /**
     * @param string $endpoint
     *
     * @throws InvalidOrMissingEndpointException
     *
     * @return $this|Endpoint
     */
    public function __get($endpoint)
    {
        switch ($endpoint) {
            case 'orders':
                $client = $this->client;

                if (empty($client->ids)) {
                    throw new InvalidOrMissingEndpointException('The orders endpoint on customers requires a customer ID. e.g. $api->customers(123)->orders->get()');
                }

                $client->queue[] = [$client->api, $client->ids[0] ?? null];
                $client->api = 'orders';
                $client->ids = [];

                return $this;
            default:
                return parent::__get($endpoint);
        }
    }
}
