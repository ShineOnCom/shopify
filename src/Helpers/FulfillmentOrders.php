<?php

namespace Dan\Shopify\Helpers;

use Dan\Shopify\ArrayGraphQL;
use Dan\Shopify\Exceptions\InvalidGraphQLCallException;
use Dan\Shopify\Util;
use Illuminate\Support\Arr;

class FulfillmentOrders extends Endpoint
{
    public function graphQLEnabled()
    {
        return parent::useGraphQL('fulfillment_orders');
    }

    public function makeGraphQLQuery(): array
    {
        return $this->dto->mutate ? $this->getMutation() : $this->getQuery();
    }

    private function getFields()
    {
        return [
            'id',
            'createdAt',
            'updatedAt',
            'assignedLocation' => [
                'name',
                'address1',
                'address2',
                'city',
                'countryCode',
                'phone',
                'province',
                'zip',
            ],
            'requestStatus',
            'status',
            'fulfillAt',
            'supportedActions' => [
                'action',
            ],
            'destination' => [
                'id',
                'address1',
                'address2',
                'city',
                'company',
                'countryCode',
                'email',
                'firstName',
                'lastName',
                'phone',
                'province',
                'zip',
            ],
            'internationalDuties' => [
                'incoterm',
            ],
            'lineItems($PER_PAGE)' => [
                'edges' => [
                    'node' => [
                        'id',
                        'totalQuantity',
                        'remainingQuantity',
                        'lineItem' => [
                            'id',
                            'variant' => [
                                'id',
                                'title',
                            ],
                        ],
                        'inventoryItemId',
                    ],
                ],
            ],
            'fulfillmentHolds' => [
                'reason',
                'reasonNotes',
                'displayReason',
                'heldBy',
                'heldByRequestingApp',
            ],
            'fulfillBy',
            'deliveryMethod' => [
                'id',
                'methodType',
                'minDeliveryDateTime',
                'maxDeliveryDateTime',
            ],
            'merchantRequests($PER_PAGE)' => [
                'edges' => [
                    'node' => [
                        'id',
                        'kind',
                        'message',
                        'requestOptions',
                        'responseData',
                        'sentAt',
                    ],
                ],
            ],
        ];
    }

    private function getOrderFields()
    {
        return [
            'order($ORDER_ID)' => [
                'id',
                'fulfillmentOrders($PER_PAGE)' => [
                    'edges' => [
                        'node' => $this->getFields(),
                    ],
                ],
            ],
        ];
    }

    private function getFulfillmentOrderFields()
    {
        return [
            'fulfillmentOrder($ID)' => $this->getFields(),
        ];
    }

    private function getQuery()
    {
        if ($this->dto->hasResourceInQueue('orders')) {
            return $this->getOrderQuery();
        }

        return [
            'query' => ArrayGraphQL::convert($this->getFulfillmentOrderFields(), [
                '$ID' => Util::toGraphQLIdParam($this->dto->getResourceId(), 'FulfillmentOrder'),
                '$PER_PAGE' => 'first: 100',
            ]),
            'variables' => null,
        ];
    }

    private function getOrderQuery()
    {
        $orderId = $this->dto->findResourceIdInQueue('orders');

        return [
            'query' => ArrayGraphQL::convert($this->getOrderFields(), [
                '$ORDER_ID' => Util::toGraphQLIdParam($orderId, 'Order'),
                '$PER_PAGE' => 'first: 100',
            ]),
            'variables' => null,
        ];
    }

    private function getMutation(): array
    {
        if ($this->dto->hasResourceInQueue('fulfillment_request')) {
            return $this->getFulfillmentRequestMutation();
        }

        if ($this->dto->hasResourceInQueue('fulfillment_request/accept')) {
            return $this->getFulfillmentRequestAcceptMutation();
        }

        if ($this->dto->hasResourceInQueue('fulfillment_request/reject')) {
            return $this->getFulfillmentRequestRejectMutation();
        }

        if ($this->dto->hasResourceInQueue('release_hold')) {
            return $this->getFulfillmentRequestReleaseHoldMutation();
        }

        throw new InvalidGraphQLCallException('Mutation for Fulfillment Order not implemented');
    }

    private function getFulfillmentRequestMutation(): array
    {
        $query = [
            'fulfillmentOrderSubmitFulfillmentRequest($INPUT)' => [
                'submittedFulfillmentOrder' => $this->getFields(),
                'userErrors' => [
                    'field',
                    'message',
                ],
            ],
        ];

        $variables = [
            'id' => $this->dto->getResourceId('FulfillmentOrder'),
            'message' => Arr::get($this->dto->payload, 'message', 'Fulfillment Request'),
            'notifyCustomer' => true,
        ];

        $query = ArrayGraphQL::convert(
            $query,
            ['$INPUT' => 'id: $id, message: $message, notifyCustomer: $notifyCustomer', '$PER_PAGE' => 'first: 100'],
            'mutation fulfillmentOrderSubmitFulfillmentRequest($id: ID!, $message: String, $notifyCustomer: Boolean)'
        );

        return [
            'query' => $query,
            'variables' => $variables,
        ];
    }

    private function getFulfillmentRequestAcceptMutation(): array
    {
        $query = [
            'fulfillmentOrderAcceptFulfillmentRequest($INPUT)' => [
                'fulfillmentOrder' => $this->getFields(),
                'userErrors' => [
                    'field',
                    'message',
                ],
            ],
        ];

        $variables = [
            'id' => $this->dto->getResourceId('FulfillmentOrder'),
            'message' => Arr::get($this->dto->payload, 'message', 'Fulfillment Request'),
        ];

        $query = ArrayGraphQL::convert(
            $query,
            ['$INPUT' => 'id: $id, message: $message', '$PER_PAGE' => 'first: 100'],
            'mutation fulfillmentOrderAcceptFulfillmentRequest($id: ID!, $message: String)'
        );

        return [
            'query' => $query,
            'variables' => $variables,
        ];
    }

    private function getFulfillmentRequestRejectMutation(): array
    {
        $query = [
            'fulfillmentOrderRejectFulfillmentRequest($INPUT)' => [
                'fulfillmentOrder' => $this->getFields(),
                'userErrors' => [
                    'field',
                    'message',
                ],
            ],
        ];

        $variables = [
            'id' => $this->dto->getResourceId('FulfillmentOrder'),
            'message' => Arr::get($this->dto->payload, 'message', 'Fulfillment Request'),
        ];

        $query = ArrayGraphQL::convert(
            $query,
            ['$INPUT' => 'id: $id, message: $message', '$PER_PAGE' => 'first: 100'],
            'mutation fulfillmentOrderAcceptFulfillmentRequest($id: ID!, $message: String)'
        );

        return [
            'query' => $query,
            'variables' => $variables,
        ];
    }

    private function getFulfillmentRequestReleaseHoldMutation(): array
    {
        $query = [
            'fulfillmentOrderReleaseHold($INPUT)' => [
                'fulfillmentOrder' => $this->getFields(),
                'userErrors' => [
                    'field',
                    'message',
                ],
            ],
        ];

        $variables = [
            'id' => $this->dto->getResourceId('FulfillmentOrder'),
        ];

        $query = ArrayGraphQL::convert(
            $query,
            ['$INPUT' => 'id: $id', '$PER_PAGE' => 'first: 100'],
            'mutation fulfillmentOrdersReleaseHold($id: ID!)'
        );

        return [
            'query' => $query,
            'variables' => $variables,
        ];
    }

    public function accept($payload = [])
    {
        return $this->client->post($payload, 'fulfillment_request/accept');
    }

    public function cancel($id = null)
    {
        $path = is_null($id) ? 'cancel' : "{$id}/cancel";

        return $this->client->post([], $path);
    }

    public function close($payload = [])
    {
        return $this->client->post($payload, 'close');
    }

    public function move($payload = [])
    {
        return $this->client->post($payload, 'move');
    }

    public function open($payload = [])
    {
        return $this->client->post($payload, 'open');
    }

    public function reject($payload = [])
    {
        return $this->client->post($payload, 'fulfillment_request/reject');
    }

    public function release_hold()
    {
        return $this->client->post([], 'release_hold');
    }

    public function reschedule($payload = [])
    {
        return $this->client->post($payload, 'reschedule');
    }
}
