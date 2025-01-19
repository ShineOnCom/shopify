<?php

namespace Dan\Shopify\Helpers;

use Dan\Shopify\ArrayGraphQL;
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

        return $this->saveMutation();
    }

    private function saveMutation(): array
    {
        $header = $this->dto->getResourceId() ? 'productUpdate($PRODUCT_INPUT)' : 'productCreate($PRODUCT_INPUT)';

        $query = [
            $header => [
                'product' => $this->getFields(),
                'userErrors' => [
                    'field',
                    'message',
                ],
            ],
        ];

        return [
            'query' => ArrayGraphQL::convert(
                $query,
                [
                    '$PRODUCT_INPUT' => 'input: $input',
                    '$PER_PAGE' => 'first: 250',
                ],
                'mutation SaveProduct($input: ProductInput!)'
            ),
            'variables' => [
                'input' => $this->getVariables(),
            ],
        ];
    }

    private function getVariables(): array
    {
        $variables = [];
        $variables = Util::convertKeysToCamelCase(Arr::get($this->dto->payload, 'product'));
        if ($this->dto->getResourceId()) {
            $variables['id'] = $this->dto->getResourceId('Product');
        }

        $this
            ->formatOptionsVariableForMutation($variables)
            ->mapFields($variables);

        return $variables;
    }

    private function mapFields(&$variables): self
    {
        $supportedKeysInProductInput = [
            'category' => 'category',
            'claimOwnership' => 'claimOwnership',
            'collectionsToJoin' => 'collectionsToJoin',
            'collectionsToLeave' => 'collectionsToLeave',
            'combinedListingRole' => 'combinedListingRole',
            'bodyHtml' => 'descriptionHtml',
            'giftCard' => 'giftCard',
            'giftCardTemplateSuffix' => 'giftCardTemplateSuffix',
            'handle' => 'handle',
            'metafields' => 'metafields',
            'options' => 'productOptions',
            'productType' => 'productType',
            'redirectNewHandle' => 'redirectNewHandle',
            'requiresSellingPlan' => 'requiresSellingPlan',
            'seo' => 'seo',
            'status' => 'status',
            'tags' => 'tags',
            'tempalteSuffix' => 'tempalteSuffix',
            'title' => 'title',
            'vendor' => 'vendor',
        ];

        foreach ($supportedKeysInProductInput as $key => $map) {
            if (Arr::get($variables, $key)) {
                $variables[$map] = $variables[$key];
            }

            unset($variables[$key]);
        }

        return $this;
    }

    private function formatOptionsVariableForMutation(&$variables): self
    {
        if ($options = Arr::get($variables, 'productOptions')) {
            $options = is_array($options[0]) ? $options : [$options];
            $options = array_map(function ($option) {
                $option['values'] = array_map(fn ($value) => ['name' => $value], $option['values']);

                return $option;
            }, $options);

            $variables['productOptions'] = $options;
        }

        return $this;
    }

    private function formatVariantsVariableForMutation(&$variables): self
    {
        $variables['variants'] = array_map(function ($variant) {
            $variant['id'] = Util::toGid($variant['id'], 'ProductVariant');
            if ($variant['fulfillmentServiceId']) {
                $variant['inventoryItem'] = ['fulfillmentServiceId' => Util::toGid($variant['fulfillmentServiceId'], 'FulfillmentService')];
                unset($variant['fulfillmentService']);
                unset($variant['fulfillmentServiceId']);
            }

            return $variant;
        }, $variables['variants']);

        return $this;
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
