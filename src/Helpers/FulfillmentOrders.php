<?php

namespace Dan\Shopify\Helpers;

use Dan\Shopify\ArrayGraphQL;
use Dan\Shopify\Exceptions\InvalidGraphQLCallException;
use Dan\Shopify\Util;

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
            'order($ORDER_ID)' => [
                'id',
                'fulfillmentOrders($PER_PAGE)' => [
                    'edges' => [
                        'node' => [
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
                        ],
                    ],
                ],
            ],
        ];
    }

    private function getQuery()
    {
        $orderId = $this->dto->findResourceIdInQueue('orders');

        return [
            'query' => ArrayGraphQL::convert($this->getFields(), [
                '$ORDER_ID' => Util::toGraphQLIdParam($orderId, 'Order'),
                '$PER_PAGE' => 'first: 100',
            ]),
            'variables' => null,
        ];
    }

    private function getMutation(): array
    {
        throw new InvalidGraphQLCallException('WIP');
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
