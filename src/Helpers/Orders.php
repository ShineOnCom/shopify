<?php

namespace Dan\Shopify\Helpers;

use Dan\Shopify\ArrayGraphQL;
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

        return $this->getOrders();
    }

    private function getOrders()
    {
        $header = $this->dto->payload ? 'orders($PER_PAGE, $FILTER)' : 'orders($PER_PAGE)';

        $fields = [
            $header => [
                'edges' => [
                    'node' => $this->getFields(),
                ],
            ],
        ];

        return [
            'query' => ArrayGraphQL::convert($fields, [
                '$PER_PAGE' => 'first: 50',
                '$FILTER' => $this->getFilter(),
            ]),
            'variables' => null,
        ];
    }

    private function getFilter()
    {
        if (! $this->dto->payload) {
            return null;
        }

        $filters = collect([]);
        if ($ids = Arr::get($this->dto->payload, 'ids')) {
            $ids = collect(explode(',', $ids))->map(fn ($id) => sprintf('(id:%s)', $id));
            $filters = $filters->merge($ids);
        }

        if ($name = Arr::get($this->dto->payload, 'name')) {
            $filters = $filters->merge([sprintf("(name:'%s')", $name)]);
        }

        if ($created_at_min = Arr::get($this->dto->payload, 'created_at_min')) {
            $filters = $filters->merge([sprintf("(created_at:>='%s')", $created_at_min)]);
        }

        return sprintf('query: "%s" %s', $filters->join(' OR '), $this->getSortOrder());
    }

    private function getSortOrder()
    {
        $order = Arr::get($this->dto->payload, 'order');
        if (blank($order)) {
            return '';
        }

        $order_params = explode(' ', $order);
        $field = strtoupper($order);
        $reverse = 'false';

        if (count($order_params) === 2) {
            $field = strtoupper($order_params[0]);
            $reverse = strtolower($order_params[1]) === 'asc' ? 'false' : 'true';
        }

        return sprintf('sortKey: %s, reverse: %s', $field, $reverse);
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
        throw new \Exception();
        if ($this->dto->getResourceId()) {
            return $this->updateMutation();
        }

        $payload = Arr::get($this->dto->payload, 'fulfillment_service', []);

        $query = [
            'fulfillmentServiceCreate($INPUT)' => [
                'fulfillmentService' => $this->getFields(),
                'userErrors' => [
                    'field',
                    'message',
                ],
            ],
        ];

        $variables = [
            'name' => $payload['name'],
            'callbackUrl' => $payload['callback_url'],
            'inventoryManagement' => $payload['inventory_management'],
            'trackingSupport' => $payload['tracking_support'],
        ];

        $query = ArrayGraphQL::convert(
            $query,
            [
                '$INPUT' => 'name: $name, callbackUrl: $callbackUrl, inventoryManagement: $inventoryManagement, trackingSupport: $trackingSupport',
            ],
            'mutation CreateFulfillmentService($name: String!, $callbackUrl: URL!, $inventoryManagement: Boolean!, $trackingSupport: Boolean!)'
        );

        return [
            'query' => $query,
            'variables' => $variables,
        ];
    }

    private function updateMutation(): array
    {
        throw new \Exception();
        $payload = Arr::get($this->dto->payload, 'fulfillment_service', []);

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
