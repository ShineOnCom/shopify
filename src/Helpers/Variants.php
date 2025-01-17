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
        if ($this->dto->getResourceId()) {
            return $this->updateMutation();
        }

        throw new GraphQLEnabledWithMissingQueriesException('Mutation not supported yet');
    }

    private function updateMutation(): array
    {
        $query = [
            'variantUpdate($INPUT)' => [
                'variant' => self::getFields(),
                'userErrors' => [
                    'field',
                    'message',
                ],
            ],
        ];

        $variables = Util::convertKeysToCamelCase(Arr::get($this->dto->payload, 'variant'));
        $variables['id'] = $this->dto->getResourceId('Variant');

        return [
            'query' => ArrayGraphQL::convert(
                $query,
                ['$INPUT' => 'input: $input', '$PER_PAGE' => 'first: 250'],
                'mutation UpdateVariant($input: ProductVariantInput!)'
            ),
            'variables' => ['input' => $variables],
        ];
    }
}
