<?php

namespace Dan\Shopify\Models;

use Dan\Shopify\Util;

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

    public function transformGraphQLResponse(array $response)
    {
        // Welcome to loop town!

        $response = $response['data'];
        $images = [];

        foreach ($response as $value) {
            $productId = Util::getIdFromGid($value['id']);

            foreach ($value['images']['nodes'] as $key => $media) {
                $images[] = [
                    'id' => (int) Util::getIdFromGid($media['id']),
                    'alt' => $media['altText'] ?: null,
                    'position' => $key + 1,
                    'product_id' => (int) $productId,
                    'created_at' => null,
                    'updated_at' => null,
                    'admin_graphql_api_id' => $media['id'],
                    'width' => $media['width'],
                    'height' => $media['height'],
                    'src' => $media['url'],
                    'variant_ids' => [],
                ];

                foreach ($value['variants']['edges'] as $variant) {
                    foreach ($images as $key => $productImage) {
                        $variantAlreadyAdded = in_array(Util::getIdFromGid($variant['node']['id']), $productImage['variant_ids']);
                        $variantHasProductImage = $variant['node']['image']['id'] === $productImage['admin_graphql_api_id'];

                        if ($variantHasProductImage && ! $variantAlreadyAdded) {
                            $images[$key]['variant_ids'][] = (int) Util::getIdFromGid($variant['node']['id']);
                        }
                    }
                }
            }

        }

        dd($images);

        return $images;
    }
}
