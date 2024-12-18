<?php

namespace Dan\Shopify\Helpers;

use Dan\Shopify\ArrayGraphQL;
use Dan\Shopify\Exceptions\GraphQLEnabledWithMissingQueriesException;
use Dan\Shopify\Util;

/**
 * Class FulfillmentServices.
 */
class FulfillmentServices extends Endpoint
{
    public function graphQLEnabled()
    {
        return parent::useGraphQL('fulfillment_services');
    }

    public function makeGraphQLQuery(): array
    {
        return $this->dto->mutate ? $this->getMutation() : $this->getQuery();
    }

    private function getFields()
    {
        return [
            'id',
            'callbackUrl',
            'fulfillmentOrdersOptIn',
            'permitsSkuSharing',
            'handle',
            'inventoryManagement',
            'serviceName',
        ];
    }

    private function getQuery()
    {
        if ($this->dto->getResourceId()) {
            return $this->getFulfillmentService();
        }

        return [
            'query' => ArrayGraphQL::convert($this->getFulfillmentServices()),
            'variables' => null,
        ];
    }

    private function getFulfillmentServices()
    {
        return [
            'shop' => [
                'fulfillmentServices' => $this->getFields(),
            ],
        ];
    }

    private function getFulfillmentService()
    {
        $fields = [
            'fulfillmentService($ID)' => $this->getFields(),
        ];

        return [
            'query' => ArrayGraphQL::convert($fields, [
                '$ID' => Util::toGraphQLIdParam($this->dto->getResourceId(), 'FulfillmentService'),
            ]),
            'variables' => null,
        ];
    }

    private function getMutation(): array
    {
        throw new GraphQLEnabledWithMissingQueriesException();
    }
}
