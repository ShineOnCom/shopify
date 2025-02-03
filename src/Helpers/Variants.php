<?php

namespace Dan\Shopify\Helpers;

use Dan\Shopify\ArrayGraphQL;
use Dan\Shopify\Exceptions\GraphQLEnabledWithMissingQueriesException;
use Dan\Shopify\Util;
use Illuminate\Support\Arr;

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
            'product' => [
                'id',
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
        if ($this->dto->hasResourceInQueue('delete')) {
            return $this->getDeleteMutation();
        }

        if ($this->dto->hasResourceInQueue('products')) {
            return $this->getMutationForProduct();
        }

        throw new GraphQLEnabledWithMissingQueriesException('Mutation not supported directly. Please use products');
    }

    private function getMutationForProduct()
    {
        if ($this->dto->getResourceId()) {
            return $this->getUpdateMutationForProduct();
        }

        $query = [
            'productVariantsBulkCreate($INPUT)' => [
                'product' => [
                    'id',
                ],
                'productVariants' => $this->getFields(),
                'userErrors' => [
                    'field',
                    'message',
                ],
            ],
        ];

        return [
            'query' => ArrayGraphQL::convert(
                $query,
                ['$INPUT' => 'productId: $productId, variants: $variants, strategy: $strategy', '$PER_PAGE' => 'first: 250'],
                'mutation SaveVariants($productId: ID!, $strategy: ProductVariantsBulkCreateStrategy!, $variants: [ProductVariantsBulkInput!]!)'
            ),
            'variables' => $this->getVariables(),
        ];
    }

    private function getUpdateMutationForProduct()
    {
        $query = [
            'productVariantsBulkUpdate($INPUT)' => [
                'product' => [
                    'id',
                ],
                'productVariants' => $this->getFields(),
                'userErrors' => [
                    'field',
                    'message',
                ],
            ],
        ];

        return [
            'query' => ArrayGraphQL::convert(
                $query,
                ['$INPUT' => 'productId: $productId, variants: $variants', '$PER_PAGE' => 'first: 250'],
                'mutation SaveVariants($productId: ID!, $variants: [ProductVariantsBulkInput!]!)'
            ),
            'variables' => $this->getVariables(),
        ];
    }

    private function getVariables(): array
    {
        $variants = Util::toMultiDimensionalArray($this->dto->getPayload('variant'));
        $variants = array_map(function ($variant) {
            $this
                ->formatIdVariableForMutation($variant)
                ->formatInventoryItemVariableForMutation($variant)
                ->formatOptionValuesVariableForMutation($variant)
                ->formatMetaFieldsVariableForMutation($variant)
                ->mapFields($variant);

            return $variant;
        }, Util::convertKeysToCamelCase($variants));

        return [
            'productId' => Util::toGid($this->dto->findResourceIdInQueue('products'), 'Product'),
            'strategy' => 'REMOVE_STANDALONE_VARIANT',
            'variants' => $variants,
        ];
    }

    private function mapFields(&$variant): self
    {
        $variant = Util::mapFieldsForVariable([
            'id' => 'id',
            'barcode' => 'barcode',
            'compareAtPrice' => 'compareAtPrice',
            'inventoryItem' => 'inventoryItem',
            'inventoryPolicy' => 'inventoryPolicy',
            'inventoryQuantities' => 'inventoryQuantities',
            'mediaId' => 'mediaId',
            'mediaSrc' => 'mediaSrc',
            'metafields' => 'metafields',
            'optionValues' => 'optionValues',
            'price' => 'price',
            'requiresComponents' => 'requiresComponents',
            'taxable' => 'taxable',
            'taxCode' => 'taxCode',
        ], $variant);

        return $this;
    }

    private function formatOptionValuesVariableForMutation(&$variant): self
    {
        $optionValues = [];
        foreach ([1, 2, 3] as $option) {
            $value = Arr::get($variant, "option{$option}");
            $optionName = Arr::get($variant, "option{$option}Name");

            if ($value && $optionName) {
                $optionValues[] = ['optionName' => $optionName, 'name' => $value];
            }
        }

        if (filled($optionValues)) {
            $variant['optionValues'] = $optionValues;
        } elseif (! $this->dto->getResourceId()) {
            $default = sprintf('Default Title - %s', time());
            $variant['optionValues'] = [
                ['optionName' => 'Title', 'name' => Arr::get($variant, 'option1', Arr::get($variant, 'title', $default))],
            ];
        } else {
            unset($variant['optionValues']);
        }

        return $this;
    }

    private function formatMetaFieldsVariableForMutation(&$variables): self
    {
        if ($metaFields = Arr::get($variables, 'metafields')) {
            $metaFields = array_map(function ($row) {
                $row['value'] = (string) $row['value'];

                return $row;
            }, $metaFields);

            $variables['metafields'] = $metaFields;
        }

        return $this;
    }

    private function formatInventoryItemVariableForMutation(&$variant): self
    {
        if (! isset($variant['id']) && ! isset($variant['locationId'])) {
            throw new GraphQLEnabledWithMissingQueriesException('location_id must be set in the variants Array');
        }

        if (isset($variant['id']) && isset($variant['locationId'])) {
            unset($variant['locationId']);
        }

        $inventoryItem = ['tracked' => false];

        if (isset($variant['requiresShipping'])) {
            $inventoryItem['requiresShipping'] = $variant['requiresShipping'];
        }

        if (isset($variant['locationId'])) {
            $variant['inventoryQuantities'] = [
                ['availableQuantity' => 1000000, 'locationId' => Util::toGid($variant['locationId'], 'Location')],
            ];
        }

        if (isset($variant['sku'])) {
            $inventoryItem['sku'] = $variant['sku'];
        }

        if (isset($variant['cost'])) {
            $inventoryItem['cost'] = $variant['cost'];
        }

        $variant['inventoryItem'] = $inventoryItem;

        return $this;
    }

    private function formatIdVariableForMutation(&$variant): self
    {
        if ($id = Arr::get($variant, 'id')) {
            $variant['id'] = Util::toGid($id, 'ProductVariant');
        }

        return $this;
    }

    private function getDeleteMutation(): array
    {
        $query = [
            'productVariantsBulkDelete($INPUT)' => [
                'product' => [
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
                ['$INPUT' => 'productId: $productId, variantsIds: $variantsIds'],
                'mutation DeleteProductVariant($productId: ID!, $variantsIds: [ID!]!)'
            ),
            'variables' => [
                'productId' => Util::toGid($this->dto->findResourceIdInQueue('products'), 'Product'),
                'variantsIds' => [$this->dto->getResourceId('ProductVariant')],
            ],
        ];
    }
}
