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
                    'node' => Variants::getFields(),
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
        $header = $filters ? 'productsCount($FILTERS)' : 'productsCount';

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
        $filters = $this->getFiltersAndSortOrder();
        $header = $filters ? 'products($PER_PAGE, $FILTERS)' : 'products($PER_PAGE)';

        $fields = [
            $header => [
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
                '$FILTERS' => $filters,
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
        if ($this->dto->hasResourceInQueue('delete')) {
            return $this->deleteMutation();
        }

        if ($this->dto->getResourceId()) {
            return $this->updateMutation();
        }

        throw new GraphQLEnabledWithMissingQueriesException('Mutation not supported yet');
    }

    private function updateMutation(): array
    {
        $query = [
            'productUpdate($INPUT)' => [
                'product' => $this->getFields(),
                'userErrors' => [
                    'field',
                    'message',
                ],
            ],
        ];

        $variables = Util::convertKeysToCamelCase(Arr::get($this->dto->payload, 'product'));
        $variables['id'] = $this->dto->getResourceId('Product');
        if ($variables['variants']) {
            $variables['variants'] = array_map(function ($variant) {
                $variant['id'] = Util::toGid($variant['id'], 'ProductVariant');

                return $variant;
            }, $variables['variants']);
        }

        if ($options = Arr::get($variables, 'options')) {
            unset($variables['options']);

            $options = is_array($options[0]) ? $options : [$options];
            $options = array_map(function ($option) {
                $option['values'] = array_map(fn ($value) => ['name' => $value], $option['values']);

                return $option;
            }, $options);

            $variables['productOptions'] = $options;
        }

        return [
            'query' => ArrayGraphQL::convert(
                $query,
                ['$INPUT' => 'input: $input', '$PER_PAGE' => 'first: 250'],
                'mutation UpdateProduct($input: ProductInput!)'
            ),
            'variables' => ['input' => $variables],
        ];
    }

    private function deleteMutation(): array
    {
        $query = [
            'productDelete($INPUT)' => [
                'deletedProductId',
                'userErrors' => [
                    'field',
                    'message',
                ],
            ],
        ];

        return [
            'query' => ArrayGraphQL::convert(
                $query,
                ['$INPUT' => 'input: { id: $id }'],
                'mutation DeleteProduct($id: ID!)'
            ),
            'variables' => [
                'id' => $this->dto->getResourceId('Product'),
            ],
        ];
    }
}
