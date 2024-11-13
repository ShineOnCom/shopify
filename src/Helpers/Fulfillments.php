<?php

namespace Dan\Shopify\Helpers;

use Dan\Shopify\ArrayGraphQL;

/**
 * Class Fulfillments.
 */
class Fulfillments extends Endpoint
{
    public function graphQLEnabled()
    {
        return parent::useGraphQL('fulfillments');
    }

    public function makeGraphQLQuery(array $ids, array $queue, string $append, ?array $payload = null, bool $mutate = false): array
    {
        if ($mutate) {
            return $this->getFulfillmentMutation($payload);
        }

        return $this->getFulfillmentQuery(100);
    }

    private function getFulfillmentFields()
    {
        return [
            'id',
            'createdAt',
            'estimatedDeliveryAt',
            'inTransitAt',
            'location' => [
                'id',
                'name',
                'address' => [
                    'address1',
                    'address2',
                    'city',
                    'country',
                    'zip',
                ],
            ],
            'fulfillmentLineItems' => [
                'edges' => [
                    'node' => [
                        'id',
                        'quantity',
                        'lineItem' => [
                            'id',
                            'title',
                            'variantTitle',
                            'quantity',
                        ],
                    ],
                ],
            ],
            'order' => [
                'id',
                'name',
                'email',
                'shippingAddress' => [
                    'address1',
                    'address2',
                    'city',
                    'country',
                    'zip',
                ],
            ],
            'status',
            'trackingInfo' => [
                'company',
                'number',
                'url',
            ],
            'updatedAt',
        ];
    }

    private function getFulfillmentQuery($fulfillmentId)
    {
        $query = [
            'query' => [
                'fulfillment($FULFILLMENT_ID)' => $this->getFulfillmentFields(),
            ],
        ];

        return [
            'query' => ArrayGraphQL::convert($query, ['$FULFILLMENT_ID' => sprintf('id: "gid://shopify/Fulfillment/%s"', $fulfillmentId)]),
            'variables' => null,
        ];
    }

    private function getFulfillmentMutation($payload): array
    {
        $fulfillment = $payload['fulfillment'];

        $query = [
            'fulfillmentCreate($INPUT)' => [
                'fulfillment' => $this->getFulfillmentFields(),
                'userErrors' => [
                    'field',
                    'message',
                ],
            ],
        ];

        $lineItemsByFulfillmentOrder = array_map(function ($row) {
            return [
                'fulfillmentOrderId' => sprintf('gid://shopify/Fulfillment/%s', $row['fulfillment_order_id']),
                'fulfillmentOrderLineItems' => array_map(function ($line) {
                    return [
                        'id' => sprintf('gid://shopify/FulfillmentOrderLineItem/%s', $line['id']),
                        'quantity' => $line['quantity'],
                    ];
                }, $row['fulfillment_order_line_items']),
            ];
        }, $fulfillment['line_items_by_fulfillment_order']);

        $variables = [
            'fulfillment' => [
                'lineItemsByFulfillmentOrder' => $lineItemsByFulfillmentOrder,
                'trackingInfo' => $fulfillment['tracking_info'],
                'notifyCustomer' => true,
            ],
            'message' => $fulfillment['message'],
        ];

        if (filled($fulfillment['origin_address'])) {
            $variables['fulfillment']['originAddress'] = $fulfillment['origin_address'];
        }

        $query = ArrayGraphQL::convert(
            $query,
            ['$INPUT' => 'fulfillment: $fulfillment, message: $message'],
            'mutation fulfillmentCreate($fulfillment: FulfillmentInput!, $message: String)'
        );

        return [
            'query' => $query,
            'variables' => $variables,
        ];
    }
}
