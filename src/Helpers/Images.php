<?php

namespace Dan\Shopify\Helpers;

use Dan\Shopify\ArrayGraphQL;
use Dan\Shopify\Exceptions\InvalidGraphQLCallException;
use Dan\Shopify\Util;
use Exception;

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
     * @throws \Exception
     */
    public function makeGraphQLQuery(): array
    {
        return $this->dto->mutate ? $this->getMutation() : $this->getQuery();
    }

    /**
     * @throws InvalidGraphQLCallException
     * @throws Exception
     */
    protected function getQuery(): array
    {
        if ($this->dto->hasResourceInQueue('products')) {
            return $this->constructImagesQuery();
        }

        if ($this->dto->getResourceId()) {
            return $this->constructImageQuery();
        }

        throw new InvalidGraphQLCallException('Invalid request, you must specify a resource (product, metafields etc.) or an image id.');
    }

    protected function getMutation()
    {
    }

    /**
     * @throws \Exception
     */
    public function constructImagesQuery(): array
    {
        $query = [
            'product($RESOURCE_ID)' => $this->getImagesQuery(),
        ];

        $gid = Util::toGid($this->dto->findResourceIdInQueue('products'), 'Product');

        return [
            'query' => ArrayGraphQL::convert($query, [
                '$RESOURCE_ID' => 'id: "'.$gid.'"',
                '$COUNT' => 'first: 30',
            ]),
            'variables' => null,
        ];
    }

    public function getImagesQuery(): array
    {
        return [
            'id',
            'variants($COUNT)' => [
                'edges' => [
                    'node' => [
                        'id',
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

    public function constructImageQuery(): array
    {
        $query = [
            'node($RESOURCE_ID)' => $this->getImageQuery(),
        ];

        $gid = Util::toGid($this->dto->getResourceId(), 'MediaImage');

        return [
            'query' => ArrayGraphQL::convert($query, [
                '$RESOURCE_ID' => 'id: "'.$gid.'"',
            ]),
            'variables' => null,
        ];
    }

    public function getImageQuery(): array
    {
        return [
            '... on MediaImage' => [
                'createdAt',
                'updatedAt',
                'image' => [
                    'id',
                    'url',
                    'altText',
                    'width',
                    'height',
                ],
            ],
        ];
    }
}
