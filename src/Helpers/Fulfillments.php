<?php

namespace Dan\Shopify\Helpers;

use Dan\Shopify\ArrayGraphQL;
use Dan\Shopify\Exceptions\InvalidGraphQLCallException;
use Dan\Shopify\Util;

/**
 * Class Fulfillments.
 */
class Fulfillments extends Endpoint
{
    public function graphQLEnabled()
    {
        return parent::useGraphQL('fulfillments');
    }

    public function makeGraphQLQuery(array $ids, array $queue, string $append, $payload = null, bool $mutate = false): array
    {
        if ($mutate) {
            return $this->getFulfillmentMutation($payload);
        }

        $fulfillment_id = empty($ids) ? $payload : $ids[0];
        if (empty($fulfillment_id)) {
            throw new InvalidGraphQLCallException();
        }

        return $this->getFulfillmentQuery($fulfillment_id);
    }

    private function getFulfillmentFields()
    {
        return [
            'id',
            'createdAt',
            'estimatedDeliveryAt',
            'inTransitAt',
            'fulfillmentLineItems($FULFILLMENT_LINE_ITEMS_PER_PAGE)' => [
                'edges' => [
                    'node' => [
                        'id',
                        'quantity',
                        'lineItem' => [
                            'id',
                            'title',
                            'variant' => [
                                'id',
                                'title',
                            ],
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
            'fulfillment($FULFILLMENT_ID)' => $this->getFulfillmentFields(),
        ];

        $gid = Util::toGid($fulfillmentId, 'Fulfillment');

        return [
            'query' => ArrayGraphQL::convert($query, [
                '$FULFILLMENT_ID' => sprintf('id: "%s"', $gid),
                '$FULFILLMENT_LINE_ITEMS_PER_PAGE' => 'first: 250',
            ]),
            'variables' => null,
        ];
    }

    private function getFulfillmentMutation($payload): array
    {
        $fulfillment = $payload['fulfillment'];

        $query = [
            'fulfillmentCreateV2($INPUT)' => [
                'fulfillment' => $this->getFulfillmentFields(),
                'userErrors' => [
                    'field',
                    'message',
                ],
            ],
        ];

        $lineItemsByFulfillmentOrder = array_map(function ($row) {
            return [
                'fulfillmentOrderId' => Util::toGid($row['fulfillment_order_id'], 'FulfillmentOrder'),
                'fulfillmentOrderLineItems' => array_map(function ($line) {
                    return [
                        'id' => Util::toGid($line['id'], 'FulfillmentOrderLineItem'),
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
            [
                '$INPUT' => 'fulfillment: $fulfillment, message: $message',
                '$FULFILLMENT_LINE_ITEMS_PER_PAGE' => 'first: 250',
            ],
            'mutation fulfillmentCreateV2($fulfillment: FulfillmentV2Input!, $message: String)'
        );

        return [
            'query' => $query,
            'variables' => $variables,
        ];
    }
}
