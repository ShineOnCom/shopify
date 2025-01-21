<?php

namespace Dan\Shopify\Helpers;

use Dan\Shopify\ArrayGraphQL;
use Dan\Shopify\Exceptions\GraphQLEnabledWithMissingQueriesException;
use Dan\Shopify\Util;
use Illuminate\Support\Arr;

/**
 * Class Orders.
 */
class Orders extends Endpoint
{
    public function graphQLEnabled()
    {
        return parent::useGraphQL('orders');
    }

    public function makeGraphQLQuery(): array
    {
        return $this->dto->mutate ? $this->getMutation() : $this->getQuery();
    }

    private function getFields()
    {
        $addressFields = [
            'id',
            'firstName',
            'lastName',
            'latitude',
            'longitude',
            'name',
            'phone',
            'company',
            'address1',
            'address2',
            'city',
            'province',
            'country',
            'countryCodeV2',
            'province',
            'provinceCode',
            'zip',
        ];

        $moneyFields = [
            'shopMoney' => [
                'amount',
                'currencyCode',
            ],
            'presentmentMoney' => [
                'amount',
                'currencyCode',
            ],
        ];

        return [
            'id',
            'app' => [
                'id',
            ],
            'clientIp',
            'customerAcceptsMarketing',
            'cancelReason',
            'cancelledAt',
            'confirmationNumber',
            'confirmed',
            'email',
            'createdAt',
            'closedAt',
            'currencyCode',
            'email',
            'customer' => [
                'id',
                'createdAt',
                'defaultAddress' => $addressFields,
                'email',
                'emailMarketingConsent' => [
                    'consentUpdatedAt',
                    'marketingOptInLevel',
                    'marketingState',
                ],
                'firstName',
                'lastName',
                'multipassIdentifier',
                'note',
                'phone',
                'smsMarketingConsent' => [
                    'consentCollectedFrom',
                    'consentUpdatedAt',
                    'marketingOptInLevel',
                    'marketingState',
                ],
                'state',
                'tags',
                'taxExempt',
                'taxExemptions',
                'updatedAt',
                'verifiedEmail',
                'validEmailAddress',
                'locale',
            ],
            'lineItems($PER_PAGE)' => [
                'edges' => [
                    'node' => [
                        'currentQuantity',
                        'discountAllocations' => [
                            'allocatedAmountSet' => $moneyFields,
                        ],
                        'duties' => [
                            'id',
                            'price' => $moneyFields,
                        ],
                        'nonFulfillableQuantity',
                        'fulfillmentService' => [
                            'id',
                            'handle',
                            'serviceName',
                        ],
                        'fulfillmentStatus',
                        'isGiftCard',
                        'id',
                        'name',
                        'originalUnitPriceSet' => $moneyFields,
                        'product' => [
                            'id',
                        ],
                        'requiresShipping',
                        'sku',
                        'taxLines' => [
                            'priceSet' => $moneyFields,
                            'rate',
                            'source',
                        ],
                        'taxable',
                        'totalDiscountSet' => $moneyFields,
                        'variant' => [
                            'id',
                            'inventoryQuantity',
                        ],
                        'variantTitle',
                        'vendor',
                        'title',
                        'quantity',
                    ],
                ],
            ],
            'customerLocale',
            'discountCodes',
            'discountApplications($PER_PAGE)' => [
                'edges' => [
                    'node' => [
                        'allocationMethod',
                        'index',
                        'targetSelection',
                        'targetType',
                        'value' => [
                            '__typename',
                        ],
                    ],
                ],
            ],
            'estimatedTaxes',
            'displayFinancialStatus',
            'displayFulfillmentStatus',
            'fulfillments' => [
                'id',
                'createdAt',
                'deliveredAt',
                'displayStatus',
                'fulfillmentOrders($PER_PAGE)' => [
                    'edges' => [
                        'node' => [
                            'id',
                        ],
                    ],
                ],
            ],
            'billingAddress' => $addressFields,
            'shippingAddress' => $addressFields,
            'updatedAt',
            'cartDiscountAmountSet' => $moneyFields,
            'currentSubtotalPriceSet' => $moneyFields,
            'currentTotalAdditionalFeesSet' => $moneyFields,
            'currentTotalDiscountsSet' => $moneyFields,
            'currentTotalDutiesSet' => $moneyFields,
            'currentTotalPriceSet' => $moneyFields,
            'currentTotalTaxSet' => $moneyFields,
            'totalPriceSet' => $moneyFields,
            'merchantOfRecordApp' => [
                'id',
                'name',
            ],
            'name',
            'note',
            'customAttributes' => [
                'key',
                'value',
            ],
            'poNumber',
            'confirmationNumber',
            'statusPageUrl',
            'originalTotalAdditionalFeesSet' => $moneyFields,
            'originalTotalDutiesSet' => $moneyFields,
            'paymentGatewayNames',
            'phone',
            'presentmentCurrencyCode',
            'processedAt',
            'refundable',
            'refunds' => [
                'id',
                'note',
                'createdAt',
            ],
            'sourceIdentifier',
            'sourceName',
            'registeredSourceUrl',
            'subtotalPriceSet' => $moneyFields,
            'tags',
            'taxExempt',
            'taxLines' => [
                'priceSet' => $moneyFields,
                'channelLiable',
                'rate',
                'ratePercentage',
                'source',
            ],
            'taxesIncluded',
            'test',
            'totalDiscountsSet' => $moneyFields,
            'totalOutstandingSet' => $moneyFields,
            'totalShippingPriceSet' => $moneyFields,
            'totalTaxSet' => $moneyFields,
            'totalTipReceivedSet' => $moneyFields,
            'totalWeight',
            'shippingLines($PER_PAGE)' => [
                'edges' => [
                    'node' => [
                        'carrierIdentifier',
                        'code',
                        'currentDiscountedPriceSet' => $moneyFields,
                        'custom',
                        'deliveryCategory',
                        'id',
                        'phone',
                        'originalPriceSet' => $moneyFields,
                        'requestedFulfillmentService' => [
                            'handle',
                        ],
                        'source',
                        'taxLines' => [
                            'title',
                        ],
                        'title',
                    ],
                ],
            ],
        ];
    }

    private function getQuery()
    {
        if ($this->dto->getResourceId()) {
            return $this->getOrder();
        }

        if ($this->dto->append === 'count') {
            return $this->getOrdersCount();
        }

        return $this->getOrders();
    }

    private function getOrdersCount()
    {
        $filters = $this->getFilters();
        $header = $filters ? 'ordersCount($FILTERS)' : 'ordersCount';

        $fields = [
            $header => [
                'count',
                'precision',
            ],
        ];

        return [
            'query' => ArrayGraphQL::convert($fields, ['$FILTERS' => $filters]),
            'variables' => null,
        ];
    }

    private function getOrders()
    {
        $filters = $this->getFiltersAndSortOrder();
        $header = $filters ? 'orders($PER_PAGE, $FILTERS)' : 'orders($PER_PAGE)';

        $fields = [
            $header => [
                'edges' => [
                    'node' => $this->getFields(),
                ],
                'pageInfo' => $this->getPageInfoFields(),
            ],
        ];

        $limit = Arr::get($this->dto->getPayload(), 'limit', 50);

        return [
            'query' => ArrayGraphQL::convert($fields, [
                '$PER_PAGE' => "first: {$limit}",
                '$FILTERS' => $filters,
            ]),
            'variables' => null,
        ];
    }

    private function getOrder()
    {
        $fields = [
            'order($ID)' => $this->getFields(),
        ];

        return [
            'query' => ArrayGraphQL::convert($fields, [
                '$ID' => Util::toGraphQLIdParam($this->dto->getResourceId(), 'Order'),
                '$PER_PAGE' => 'first: 250',
            ]),
            'variables' => null,
        ];
    }

    private function getMutation(): array
    {
        if ($this->dto->hasResourceInQueue('cancel')) {
            return $this->cancelMutation();
        }

        if ($this->dto->hasResourceInQueue('delete')) {
            return $this->deleteMutation();
        }

        if ($this->dto->getResourceId()) {
            return $this->updateMutation();
        }

        throw new GraphQLEnabledWithMissingQueriesException('Creating an order is currently not implemented');
    }

    private function updateMutation(): array
    {
        $query = [
            'orderUpdate($INPUT)' => [
                'order' => $this->getFields(),
                'userErrors' => [
                    'field',
                    'message',
                ],
            ],
        ];

        $variables = Util::convertKeysToCamelCase($this->dto->getPayload('order'));
        $variables['id'] = $this->dto->getResourceId('Order');

        return [
            'query' => ArrayGraphQL::convert(
                $query,
                [
                    '$INPUT' => 'input: $input',
                    '$PER_PAGE' => 'first: 250',
                ],
                'mutation UpdateOrder($input: OrderInput!)'
            ),
            'variables' => ['input' => $variables],
        ];
    }

    private function cancelMutation(): array
    {
        $query = [
            'orderCancel($INPUT)' => [
                'orderCancelUserErrors' => [
                    'field',
                    'message',
                ],
            ],
        ];

        return [
            'query' => ArrayGraphQL::convert(
                $query,
                ['$INPUT' => 'orderId: $id, reason: $reason, refund: false, restock: false'],
                'mutation CancelOrder($id: ID!, $reason: OrderCancelReason!)'
            ),
            'variables' => [
                'id' => $this->dto->getResourceId('Order'),
                'reason' => 'OTHER',
            ],
        ];
    }

    private function deleteMutation(): array
    {
        $query = [
            'orderClose($INPUT)' => [
                'order' => [
                    'id',
                    'closed',
                    'closedAt',
                ],
                'userErrors' => [
                    'field',
                    'message',
                ],
            ],
        ];

        return [
            'query' => ArrayGraphQL::convert(
                $query,
                ['$INPUT' => 'input: $input'],
                'mutation CloseOrder($input: OrderCloseInput!)'
            ),
            'variables' => [
                'input' => [
                    'id' => $this->dto->getResourceId('Order'),
                ],
            ],
        ];
    }
}
