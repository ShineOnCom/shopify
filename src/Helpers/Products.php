<?php

namespace Dan\Shopify\Helpers;

use Dan\Shopify\ArrayGraphQL;
use Dan\Shopify\Exceptions\GraphQLEnabledWithMissingQueriesException;
use Dan\Shopify\Util;
use Illuminate\Support\Arr;

/**
 * Class Products.
 */
class Products extends Endpoint
{
    public function graphQLEnabled()
    {
        return parent::useGraphQL('products');
    }

    public function makeGraphQLQuery(): array
    {
        return $this->dto->mutate ? $this->getMutation() : $this->getQuery();
    }

    private function getFields()
    {
        return [
            'id',
            'handle',
            'title',
            'bodyHtml',
            'vendor',
            'productType',
            'createdAt',
            'updatedAt',
            'publishedAt',
            'templateSuffix',
            'tags',
            'status',
            'options' => [
                'id',
                'name',
                'position',
                'values',
            ],
            'variants($PER_PAGE)' => [
                'edges' => [
                    'node' => [
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
                        'product' => [
                            'id',
                        ],
                        'sku',
                        'taxable',
                        'title',
                        'updatedAt',
                    ],
                ],
            ],
            'images($PER_PAGE)' => [
                'edges' => [
                    'node' => [
                        'id',
                        'src',
                        'altText',
                        'width',
                        'height',
                    ],
                ],
            ],
        ];
    }

    private function getQuery()
    {
        if ($this->dto->getResourceId()) {
            return $this->getProduct();
        }

        if ($this->dto->append === 'count') {
            return $this->getProductsCount();
        }

        return $this->getProducts();
    }

    private function getProductsCount()
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

    private function getProducts()
    {
        $fields = [
            'products($PER_PAGE)' => [
                'edges' => [
                    'node' => $this->getFields(),
                ],
                'pageInfo' => $this->getPageInfoFields(),
            ],
        ];

        $limit = min(30, Arr::get($this->dto->payload, 'limit', 30));

        return [
            'query' => ArrayGraphQL::convert($fields, [
                '$PER_PAGE' => "first: {$limit}",
            ]),
            'variables' => null,
        ];
    }

    private function getProduct()
    {
        $fields = [
            'product($ID)' => $this->getFields(),
        ];

        return [
            'query' => ArrayGraphQL::convert($fields, [
                '$ID' => Util::toGraphQLIdParam($this->dto->getResourceId(), 'Product'),
                '$PER_PAGE' => 'first: 250',
            ]),
            'variables' => null,
        ];
    }

    private function getMutation(): array
    {
        throw new GraphQLEnabledWithMissingQueriesException('Mutation not supported yet');
    }
}
