<?php

namespace Dan\Shopify\Models;

use Dan\Shopify\Util;
use Exception;

/**
 * Class Image.
 *
 * @property int $id
 * @property int $product_id
 * @property int $position
 * @property int $width
 * @property int $height
 * @property string $src
 * @property array $variant_ids
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class Image extends AbstractModel
{
    /** @var string */
    public static $resource_name = 'image';

    /** @var string */
    public static $resource_name_many = 'images';

    /** @var array */
    protected $dates = [
        'created_at',
        'updated_at',
    ];

    /** @var array */
    protected $casts = [
        'product_id' => 'int',
        'position' => 'int',
        'width' => 'int',
        'height' => 'int',
        'src' => 'string',
        'variant_ids' => 'array',
    ];

    /**
     * @throws Exception
     */
    public function transformGraphQLResponse(array $response): ?array
    {
        $flattenedResponse = Util::convertKeysToSnakeCase($response['data']);
        if (array_has($flattenedResponse, 'node')) {
            if (! $flattenedResponse['node']) {
                return [];
            }

            $node = $flattenedResponse['node'];
            $image = $node['image'];

            return [
                'id' => (int) $node['id'],
                'alt' => $image['alt_text'] ?: null,
                'position' => null,
                'product_id' => (int) null,
                'created_at' => $node['created_at'],
                'updated_at' => $node['updated_at'],
                'admin_graphql_api_id' => 'gid://shopify/MediaImage/'.$node['id'],
                'width' => $image['width'],
                'height' => $image['height'],
                'src' => $image['url'],
                'media_image_id' => Util::toGid($node['id'], 'MediaImage'),
                'variant_ids' => [],
            ];
        }

        $images = [];
        foreach ($flattenedResponse as $product) {
            foreach ($product['media'] as $key => $media) {
                $image = $media['image'];
                $productImageId = $product['images'][array_search($media['image']['url'], array_column($product['images'], 'url'))]['id'];
                $images[] = [
                    'id' => (int) $productImageId,
                    'alt' => $image['alt_text'] ?: null,
                    'position' => $key + 1,
                    'product_id' => (int) $product['id'],
                    'created_at' => $media['created_at'],
                    'updated_at' => $media['updated_at'],
                    'admin_graphql_api_id' => Util::toGid($productImageId, 'ProductImage'),
                    'width' => $image['width'],
                    'height' => $image['height'],
                    'src' => $image['url'],
                    'media_image_id' => Util::toGid($media['id'], 'MediaImage'),
                    'variant_ids' => [],
                ];

                foreach ($product['variants'] as $variant) {
                    if ($variant['image'] && $variant['image']['url'] === $media['image']['url']) {
                        $images[$key]['variant_ids'][] = (int) $variant['id'];
                    }
                }
            }
        }

        return $images;
    }
}
