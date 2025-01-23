<?php

namespace Dan\Shopify\Models;

use Dan\Shopify\Util;
use Illuminate\Support\Arr;

/**
 * Class Product.
 *
 * @property int $id
 * @property string $title
 * @property string $body_html
 * @property string $vendor
 * @property string $product_type
 * @property string $handle
 * @property \Carbon\Carbon $published_at
 * @property string $template_suffix
 * @property string $tags
 * @property string $published_scope
 * @property string $admin_graphql_api_id
 * @property array $variants
 * @property array $options
 * @property array $images
 * @property string $image
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class Product extends AbstractModel
{
    /** @var string */
    public static $resource_name = 'product';

    /** @var string */
    public static $resource_name_many = 'products';

    /** @var array */
    protected $dates = [
        'created_at',
        'updated_at',
        'published_at',
    ];

    /** @var array */
    protected $casts = [
        'variants' => 'array',
        'options' => 'array',
        'images' => 'array',
        'image' => 'object',
    ];

    const PUBLISHED_SCOPE_GLOBAL = 'global';

    const PUBLISHED_SCOPE_WEB = 'web';

    /** @var array */
    public static $published_scopes = [
        self::PUBLISHED_SCOPE_GLOBAL,
        self::PUBLISHED_SCOPE_WEB,
    ];

    const WEIGHT_UNIT_GRAMS = 'g';

    const WEIGHT_UNIT_KG = 'kg';

    const WEIGHT_UNIT_LB = 'lb';

    const WEIGHT_UNIT_OUNCE = 'oz';

    /** @var array */
    public static $weight_units = [
        self::WEIGHT_UNIT_GRAMS,
        self::WEIGHT_UNIT_KG,
        self::WEIGHT_UNIT_LB,
        self::WEIGHT_UNIT_OUNCE,
    ];

    public function getTagsAsArray(): array
    {
        return explode(', ', $this->tags);
    }

    public function transformGraphQLResponse(array $response): ?array
    {
        $response = Util::convertKeysToSnakeCase($response);

        if ($products_count = Arr::get($response, 'data.products_count')) {
            return $products_count;
        }

        if ($product_delete = Arr::get($response, 'data.product_delete')) {
            return $product_delete;
        }

        if ($products = Arr::get($response, 'data.products')) {
            return collect($products)->map(fn ($row) => $this->format($row))->values()->all();
        }

        $product = Arr::get($response, 'data.product') ?? Arr::get($response, 'data.product_update.product') ?? Arr::get($response, 'data.product_create.product');

        return $this->format($product);
    }

    public function format(array $row)
    {
        if (blank($row)) {
            return [];
        }

        $product_id = (int) $row['id'];

        $position = 1;
        $images = array_map(function ($image) use ($product_id, $position) {
            $image['id'] = (int) $image['id'];
            $image['admin_graphql_api_id'] = Util::toGid($image['id'], 'ProductImage');
            $image['product_id'] = $product_id;
            $image['alt'] = $image['alt_text'];
            $image['position'] = $position++;
            $image['created_at'] = null;
            $image['updated_at'] = null;
            $image['published_scope'] = 'web';
            $image['variant_ids'] = [];

            return $image;
        }, Arr::get($row, 'images', []));

        $options = array_map(function ($option) use ($product_id) {
            $option['id'] = (int) $option['id'];
            $option['product_id'] = $product_id;

            return $option;
        }, Arr::get($row, 'options', []));

        $variants = array_map(function ($variant) use ($product_id) {
            $variant = Variant::format($variant, $product_id);
            $variant['product_id'] = $product_id;

            return $variant;
        }, Arr::get($row, 'variants', []));

        $row['id'] = $product_id;
        $row['admin_graphql_api_id'] = Util::toGid($row['id'], 'Product');
        $row['options'] = $options;
        $row['images'] = $images;
        $row['variants'] = $variants;
        $row['image'] = array_first($images) ?? [];
        $row['image']['variant_ids'] = array_map(fn ($variant) => $variant['id'], $variants);
        $row['tags'] = implode(',', Arr::get($row, 'tags', []));

        return $row;
    }
}
