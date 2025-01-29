<?php

namespace Dan\Shopify\Helpers;

use Dan\Shopify\ArrayGraphQL;
use Dan\Shopify\DTOs\RequestArgumentDTO;
use Dan\Shopify\Exceptions\GraphQLRequestException;
use Dan\Shopify\Shopify;
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

    public function handleCallback(Shopify $shopify, RequestArgumentDTO $dto, array $response): array
    {
        $variantsResponse = $this->graphQL(
            $shopify,
            'variants',
            $this->getVariantsPayload($dto),
            null,
            true,
            ['products', $response['id']]
        );

        // If we are updating no need to publish to online store just skip
        if ($dto->getResourceId()) {
            if (filled($variantsResponse)) {
                return ['variants' => $variantsResponse];
            }

            return [];
        }

        $publishResponse = $this->graphQL(
            $shopify,
            'products',
            [],
            null,
            true,
            ['publish', $response['id']]
        );

        return ['variants' => $variantsResponse, 'publish' => $publishResponse];
    }

    private function getVariantsPayload(RequestArgumentDTO $dto)
    {
        $productOptions = Arr::get($dto->getPayload('product'), 'options', []);
        $variants = Arr::get($dto->getPayload('product'), 'variants', []);

        return array_map(function ($variant) use ($productOptions) {
            foreach ([1, 2, 3] as $position) {
                $option = array_values(Arr::where($productOptions, fn ($row) => (int) Arr::get($row, 'position') === $position));
                if ($name = Arr::get($option, '0.name')) {
                    $variant["option{$position}Name"] = $name;
                }
            }

            return $variant;
        }, $variants);
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

        $limit = min(30, Arr::get($this->dto->getPayload(), 'limit', 30));

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

        if ($this->dto->hasResourceInQueue('publish')) {
            return $this->publishMutation();
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
            'hasCallback' => true,
        ];
    }

    private function getVariables(): array
    {
        $variables = [];

        $variables = Util::convertKeysToCamelCase($this->dto->getPayload('product'));
        if ($this->dto->getResourceId()) {
            $variables['id'] = $this->dto->getResourceId('Product');
        }

        $this
            ->formatOptionsVariableForMutation($variables)
            ->formatStatusVariableForMutation($variables)
            ->mapFields($variables);

        return $variables;
    }

    private function mapFields(&$variables): self
    {
        $variables = Util::mapFieldsForVariable([
            'id' => 'id',
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
            'templateSuffix' => 'templateSuffix',
            'title' => 'title',
            'vendor' => 'vendor',
        ], $variables);

        return $this;
    }

    private function formatOptionsVariableForMutation(&$variables): self
    {
        // product_options cannot be specified during update
        if ($this->dto->getResourceId()) {
            unset($variables['options']);

            return $this;
        }

        if ($options = Arr::get($variables, 'options')) {
            $options = array_map(function ($option) {
                $option['values'] = array_map(fn ($value) => ['name' => $value], $option['values']);

                return $option;
            }, $options);

            $variables['options'] = $options;
        }

        return $this;
    }

    private function formatStatusVariableForMutation(&$variables): self
    {
        $variables['status'] = 'ACTIVE';

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

    private function publishMutation(): array
    {
        $query = [
            'publishablePublish($INPUT)' => [
                'userErrors' => [
                    'field',
                    'message',
                ],
            ],
        ];

        $publications = $this->graphQL($this->dto->shopify, 'publications');
        if (empty($publications)) {
            throw new GraphQLRequestException('No publications available for Store');
        }

        $publication = Arr::first($publications);

        return [
            'query' => ArrayGraphQL::convert(
                $query,
                ['$INPUT' => 'id: $id, input: {publicationId: $publicationId, publishDate: $publishDate}'],
                'mutation PublishProduct($id: ID!, $publicationId: ID!, $publishDate: DateTime!)'
            ),
            'variables' => [
                'id' => Util::toGid($this->dto->findResourceIdInQueue('products'), 'Product'),
                'publicationId' => Util::toGid(Arr::get($publication, 'id'), 'Publication'),
                'publishDate' => now()->toIso8601String(),
            ],
        ];
    }
}
