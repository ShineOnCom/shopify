<?php

namespace Dan\Shopify\Helpers;

use Dan\Shopify\ArrayGraphQL;
use Dan\Shopify\Exceptions\InvalidGraphQLCallException;
use Dan\Shopify\Util;

/**
 * Class Images.
 */
class Images extends Endpoint
{
    public function graphQLEnabled(): bool
    {
        return parent::useGraphQL('images');
    }

    /**
     * @throws InvalidGraphQLCallException
     */
    public function makeGraphQLQuery(): array
    {
        return $this->dto->mutate ? $this->getMutation() : $this->getQuery($this->dto->findResourceIdInQueue('products'));
    }

    private function getQuery($id): array
    {
        $query = [
            'product($RESOURCE_ID)' => $this->getFields(),
        ];

        $gid = Util::toGid($id, 'Product');

        return [
            'query' => ArrayGraphQL::convert($query, [
                '$RESOURCE_ID' => 'id: "'.$gid.'"',
                '$COUNT' => 'first: 100',
            ]),
            'variables' => null,
        ];
    }

    private function getFields(): array
    {

        return [
            'id',
            'images($COUNT)' => [
                'nodes' => [
                    'id',
                    'url',
                    'altText',
                    'width',
                    'height',
                ],
            ],
            'variants($COUNT)' => [
                'edges' => [
                    'node' => [
                        'id',
                        'availableForSale',
                        'barcode',
                        'compareAtPrice',
                        'createdAt',
                        'updatedAt',
                        'defaultCursor',
                        'displayName',
                        'position',
                        'price',
                        'sellableOnlineQuantity',
                        'sku',
                        'taxCode',
                        'taxable',
                        'title',
                        'image' => [
                            'id',
                            'url',
                            'altText',
                            'width',
                            'height',
                        ],
                    ],
                ],
            ],
        ];
    }

    protected function getMutation()
    {
    }
}
