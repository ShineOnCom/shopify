<?php

namespace Dan\Shopify\Helpers;

use Dan\Shopify\ArrayGraphQL;
use Dan\Shopify\Util;
use Illuminate\Support\Arr;

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
        if ($this->dto->getResourceId()) {
            return $this->updateMutation();
        }

        $payload = $this->dto->getPayload('fulfillment_service');

        $query = [
            'fulfillmentServiceCreate($INPUT)' => [
                'fulfillmentService' => $this->getFields(),
                'userErrors' => [
                    'field',
                    'message',
                ],
            ],
        ];

        $query = ArrayGraphQL::convert(
            $query,
            [
                '$INPUT' => 'name: $name, callbackUrl: $callbackUrl, inventoryManagement: $inventoryManagement, trackingSupport: $trackingSupport, permitsSkuSharing: $permitsSkuSharing',
            ],
            'mutation CreateFulfillmentService($name: String!, $callbackUrl: URL!, $inventoryManagement: Boolean!, $trackingSupport: Boolean!, $permitsSkuSharing: Boolean!)'
        );

        return [
            'query' => $query,
            'variables' => Util::convertKeysToCamelCase($payload),
        ];
    }

    private function updateMutation(): array
    {
        $payload = $this->dto->getPayload('fulfillment_service');

        $query = [
            'fulfillmentServiceUpdate($INPUT)' => [
                'fulfillmentService' => $this->getFields(),
                'userErrors' => [
                    'field',
                    'message',
                ],
            ],
        ];

        $variables = [
            'id' => $this->dto->getResourceId('FulfillmentService'),
            'name' => $payload['name'],
            'callbackUrl' => $payload['callback_url'],
            'inventoryManagement' => $payload['inventory_management'],
            'trackingSupport' => $payload['tracking_support'],
            'permitsSkuSharing' => Arr::get($payload, 'permits_sku_sharing', true),
        ];

        $query = ArrayGraphQL::convert(
            $query,
            [
                '$INPUT' => 'id: $id, name: $name, callbackUrl: $callbackUrl, inventoryManagement: $inventoryManagement, trackingSupport: $trackingSupport',
            ],
            'mutation CreateFulfillmentService($id: ID!, $name: String!, $callbackUrl: URL!, $inventoryManagement: Boolean!, $trackingSupport: Boolean!)'
        );

        return [
            'query' => $query,
            'variables' => $variables,
        ];
    }
}
