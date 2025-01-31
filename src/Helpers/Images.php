<?php

namespace Dan\Shopify\Helpers;

use Dan\Shopify\ArrayGraphQL;
use Dan\Shopify\Exceptions\GraphQLEnabledWithMissingQueriesException;
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
        if ($this->dto->hasResourceInQueue('products') && ! $this->dto->getResourceId()) {
            return $this->constructImagesQuery();
        }

        if ($this->dto->getResourceId()) {
            return $this->constructImageQuery();
        }

        throw new InvalidGraphQLCallException('Invalid request, you must specify a resource (product, metafields etc.) or an image id.');
    }

    /**
     * @throws GraphQLEnabledWithMissingQueriesException
     */
    private function getMutation(): array
    {
        if ($this->dto->hasResourceInQueue('delete')) {
            return $this->deleteMutation();
        }

        if ($this->dto->getResourceId()) {
            return $this->updateMutation();
        }

        throw new GraphQLEnabledWithMissingQueriesException('Creating a image is currently not implemented');
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
            'media($COUNT)' => [
                'nodes' => [
                    'id',
                    '... on MediaImage' => [
                        'id',
                        'createdAt',
                        'updatedAt',
                        'image' => [
                            'url',
                            'altText',
                            'width',
                            'height',
                        ],
                    ],
                ],
            ],
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
                'id',
                'createdAt',
                'updatedAt',
                'image' => [
                    'url',
                    'altText',
                    'width',
                    'height',
                ],
            ],
        ];
    }

    private function deleteMutation(): array
    {
        $fields = [
            'productDeleteMedia' => [
                'productDeleteMedia(mediaIds: [$mediaId], productId: $productId)' => [
                    'deletedMediaIds',
                    'deletedProductImageIds',
                    'mediaUserErrors' => [
                        'field',
                        'message',
                    ],
                    'product' => [
                        'id',
                        'title',
                        'media' => [
                            'nodes' => [
                                'alt',
                                'mediaContentType',
                                'status',
                            ],
                        ],
                    ],
                ],
            ],
        ];

        return [
            'query' => ArrayGraphQL::convert(
                $fields,
                [
                    '$mediaId' => 'mediaId: $mediaId',
                    '$productId' => 'productId: $productId',
                ],
                'mutation DeleteMediaImage'
            ),
            'variables' => [
                'mediaId' => Util::toGid($this->dto->getResourceId(), 'MediaImage'),
                'productId' => Util::toGid($this->dto->findResourceIdInQueue('products'), 'Product'),
            ],
        ];
    }

    private function updateMutation()
    {
        $fields = [
            'fileUpdate' => [
                'fileUpdate(files: $input)' => [
                    'files' => [
                        '... on MediaImage' => [
                            'id',
                            'image' => [
                                'url',
                            ],
                        ],
                    ],
                    'userErrors' => [
                        'message',
                    ],
                ],
            ],
        ];

        $query = ArrayGraphQL::convert(
            $fields,
            [
                '$input' => [
                    'id' => Util::toGid($this->dto->getResourceId(), 'MediaImage'),
                ],
            ],
            'mutation fileUpdate',
        );

        return $query;
    }
}
