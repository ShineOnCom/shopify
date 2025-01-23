<?php

namespace Dan\Shopify\Models;

use Dan\Shopify\Util;
use Illuminate\Support\Arr;

/**
 * Class Variant.
 *
 * @property int $id
 * @property int $product_id
 * @property string $title
 * @property float $price
 * @property string $sku
 * @property int $position
 * @property string $inventory_policy
 * @property float $compare_at_price
 * @property string $fulfillment_service
 * @property string $inventory_management
 * @property string $option1
 * @property string $option2
 * @property string $option3
 * @property bool $taxable
 * @property string $barcode
 * @property int $grams
 * @property int $image_id
 * @property int $inventory_quantity
 * @property float $weight
 * @property string $weight_unit
 * @property int $inventory_item_id
 * @property string $tax_code
 * @property int $old_inventory_quantity
 * @property bool $requires_shipping
 * @property array $metafields
 * @property string $admin_graphql_api_id
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class Variant extends AbstractModel
{
    /** @var string */
    public static $resource_name = 'variant';

    /** @var string */
    public static $resource_name_many = 'variants';

    /** @var array */
    protected $dates = [
        'created_at',
        'updated_at',
    ];

    /** @var array */
    protected $casts = [
        'product_id' => 'string',
        'title' => 'string',
        'price' => 'float',
        'sku' => 'string',
        'position' => 'int',
        'inventory_policy' => 'string',
        'compare_at_price' => 'float',
        'fulfillment_service' => 'string',
        'option1' => 'string',
        'taxable' => 'bool',
        'grams' => 'int',
        'image_id' => 'string',
        'inventory_quantity' => 'int',
        'weight' => 'float',
        'weight_unit' => 'string',
        'inventory_item_id' => 'string',
        'old_inventory_quantity' => 'int',
        'requires_shipping' => 'bool',
        'metafields' => 'array',
    ];

    public function transformGraphQLResponse(array $response): ?array
    {
        $response = Util::convertKeysToSnakeCase($response);

        if ($variants = Arr::get($response, 'data.product_variants_bulk_create.product_variants')) {
            return array_map(fn ($row) => $this->format($row), $variants);
        }

        return self::format(Arr::get($response, 'data.node'));
    }

    public static function format(array $row)
    {
        if (blank($row)) {
            return [];
        }

        $row['id'] = (int) $row['id'];
        $row['admin_graphql_api_id'] = Util::toGid($row['id'], 'ProductVariant');
        $row['product_id'] = null;

        $location = collect(Arr::get($row, 'inventory_item.inventory_levels'))->first();

        $row['fulfillment_service'] = Arr::get($location, 'location.fulfillment_service.handle');
        $row['inventory_item_id'] = (int) Util::getIdFromGid(Arr::get($row, 'inventory_item.id'));
        $row['inventory_management'] = null;
        $row['grams'] = null;
        $row['image_id'] = null;
        $row['old_inventory_quantity'] = null;
        $row['requires_shipping'] = Arr::get($row, 'inventory.requires_shipping');
        $row['weight'] = Arr::get($row, 'inventory_item.measurement.weight.value');
        $row['weight_unit'] = Arr::get($row, 'inventory_item.measurement.weight.unit');

        $option_id = 1;
        foreach (Arr::get($row, 'selected_options', []) as $option) {
            $row["option{$option_id}"] = $option['value'];
            $option_id++;
        }

        return $row;
    }
}
