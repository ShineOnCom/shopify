<?php

namespace Dan\Shopify\Models;

use Dan\Shopify\Util;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

/**
 * Class FulfillmentService.
 *
 * @property int $id
 * @property string $name
 * @property string $handle
 * @property string $email
 * @property bool $include_pending_stock
 * @property bool $requires_shipping_method
 * @property string $service_name
 * @property bool $inventory_management
 * @property bool $tracking_support
 * @property int $provider_id
 * @property int $location_id
 */
class FulfillmentService extends AbstractModel
{
    /** @var string */
    public static $resource_name = 'fulfillment_service';

    /** @var string */
    public static $resource_name_many = 'fulfillment_services';

    /** @var array */
    protected $dates = [];

    /** @var array */
    protected $casts = [
        'id' => 'integer',
        'name' => 'string',
        'handle' => 'string',
        'email' => 'string',
        'include_pending_stock' => 'bool',
        'requires_shipping_method' => 'bool',
        'service_name' => 'string',
        'inventory_management' => 'bool',
        'tracking_support' => 'bool',
        'provider_id' => 'integer',
        'location_id' => 'integer',
    ];

    public function transformGraphQLResponse(array $response): ?array
    {
        $response = Util::convertKeysToSnakeCase($response);
        if ($fulfillment_services = Arr::get($response, 'data.shop.fulfillment_services')) {
            return collect($fulfillment_services)
                ->reject(fn ($row) => $row['id'] === 'manual')
                ->map(fn ($row) => $this->formatFulfillmentService($row))
                ->values()
                ->all();
        }

        $fulfillment_service = Arr::get($response, 'data.fulfillment_service_create.fulfillment_service')
            ?? Arr::get($response, 'data.fulfillment_service_update.fulfillment_service')
            ?? Arr::get($response, 'data.fulfillment_service', []);

        return $this->formatFulfillmentService($fulfillment_service);
    }

    public function formatFulfillmentService(array $row)
    {
        return [
            'id' => Str::replace('?id=true', '', $row['id']),
            'name' => $row['service_name'],
            'email' => null,
            'service_name' => $row['service_name'],
            'handle' => $row['handle'],
            'fulfillment_orders_opt_in' => $row['fulfillment_orders_opt_in'],
            'include_pending_stock' => null,
            'provider_id' => null,
            'location_id' => null,
            'callback_url' => $row['callback_url'],
            'tracking_support' => null,
            'inventory_management' => $row['inventory_management'],
            'permits_sku_sharing' => $row['permits_sku_sharing'],
            'requires_shipping_method' => null,
        ];
    }
}
