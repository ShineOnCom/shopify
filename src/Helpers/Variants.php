<?php

namespace Dan\Shopify\Helpers;

use Dan\Shopify\ArrayGraphQL;
use Dan\Shopify\Exceptions\GraphQLEnabledWithMissingQueriesException;
use Dan\Shopify\Util;

/**
 * Class Variants.
 */
class Variants extends Endpoint
{
    public function graphQLEnabled()
    {
        return parent::useGraphQL('variants');
    }

    public function makeGraphQLQuery(): array
    {
        return $this->dto->mutate ? $this->getMutation() : $this->getQuery();
    }

    public static function getFields()
    {
        return [
            'barcode',
            'compareAtPrice',
            'createdAt',
            'selectedOptions' => [
                'name',
                'value',
            ],
            'inventoryItem' => [
                'id',
                'requiresShipping',
                'measurement' => [
                    'id',
                    'weight' => [
                        'unit',
                        'value',
                    ],
                ],
                'inventoryLevels($PER_PAGE)' => [
                    'edges' => [
                        'node' => [
                            'location' => [
                                'fulfillmentService' => [
                                    'id',
                                    'handle',
                                    'serviceName',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'id',
            'image' => [
                'id',
            ],
            'inventoryPolicy',
            'inventoryQuantity',
            'position',
            'price',
            'sku',
            'taxable',
            'title',
            'updatedAt',
        ];
    }

    private function getQuery()
    {
        if ($this->dto->getResourceId()) {
            return $this->getVariant();
        }

        throw new GraphQLEnabledWithMissingQueriesException('You cannot get variants directly. Use the Products endpoint');
    }

    private function getVariant()
    {
        $fields = [
            'node($INPUT)' => [
                '... on ProductVariant' => self::getFields(),
            ],
        ];

        return [
            'query' => ArrayGraphQL::convert($fields, [
                '$INPUT' => Util::toGraphQLIdParam($this->dto->getResourceId(), 'ProductVariant'),
                '$PER_PAGE' => 'first: 250',
            ]),
            'variables' => null,
        ];
    }

    private function getMutation(): array
    {
        throw new GraphQLEnabledWithMissingQueriesException('Mutation not supported directly. Please use products');
    }

    /**
     * This is only meant to be called from the Products Helper. It does not return a graphql query but the array as this would be merged in the product class
     */
    public static function getMutationForProduct(int $productId = 0, array $variants = []): array
    {
        if (empty($variants)) {
            return ['query' => [], 'variables' => []];
        }

        $query = [
            'productVariantsBulkUpdate($VARIANT_INPUT)' => [
                'productVariants' => [
                    'id',
                ],
                'userErrors' => [
                    'field',
                    'message',
                ],
            ],
        ];

        $variants = array_map(function ($variant) {
            $variant['id'] = Util::toGid($variant['id'], 'ProductVariant');
            if ($variant['fulfillment_service_id']) {
                $variant['inventoryItem'] = ['fulfillmentServiceId' => Util::toGid($variant['fulfillment_service_id'], 'FulfillmentService')];
                unset($variant['fulfillment_service']);
                unset($variant['fulfillment_service_id']);
            }

            return $variant;
        }, $variants);

        return [
            'query' => $query,
            'variables' => ['productId' => $productId, 'variants' => $variants],
        ];
    }
}
