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
        if ($this->dto->hasResourceInQueue('products')) {
            return $this->getMutationForProduct();
        }

        throw new GraphQLEnabledWithMissingQueriesException('Mutation not supported directly. Please use products');
    }

    private function getMutationForProduct()
    {
        $query = [
            'productVariantsBulkCreate($INPUT)' => [
                'product' => [
                    'id',
                ],
                'productVariants' => [
                    'id',
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
                ['$INPUT' => 'productId: $productId, variants: $variants'],
                'mutation SaveVariants($productId: ID!, $variants: [ProductVariantsBulkInput!]!)'
            ),
            'variables' => [
                'productId' => Util::toGid($this->dto->findResourceIdInQueue('products'), 'Product'),
                'variants' => $this->formatPayload(),
            ],
        ];
    }

    private function formatPayload(): array
    {
        return array_map(function ($variant) {
            $variant['id'] = Util::toGid($variant['id'], 'ProductVariant');
            if ($variant['fulfillmentServiceId']) {
                $variant['inventoryItem'] = ['fulfillmentServiceId' => Util::toGid($variant['fulfillmentServiceId'], 'FulfillmentService')];
                unset($variant['fulfillmentService']);
                unset($variant['fulfillmentServiceId']);
            }

            return $variant;
        }, $this->dto->getPayload('variants'));
    }
}
