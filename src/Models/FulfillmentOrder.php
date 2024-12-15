<?php

namespace Dan\Shopify\Models;

use Dan\Shopify\Util;
use Illuminate\Support\Arr;

class FulfillmentOrder extends AbstractModel
{
    /** @var string */
    public static $resource_name = 'fulfillment_order';

    /** @var string */
    public static $resource_name_many = 'fulfillment_orders';

    public function transformGraphQLResponse(array $response): ?array
    {
        $response = Util::convertKeysToSnakeCase($response);
        $data = Arr::get($response, 'data');
        $fulfillment_orders = Arr::get($data, 'order.fulfillment_orders');
        if (! $fulfillment_orders) {
            $first_key = array_key_first($data);

            return $this->transformFulfillmentOrder(
                Arr::get($data, $first_key),
                Arr::get($data, sprintf('%s.id', $first_key))
            );
        }

        $orderId = Arr::get($data, 'order.id');
        $fulfillmentOrderId = Arr::get($data, 'order.fulfillment_orders.0.id');

        return array_map(fn ($row) => $this->transformFulfillmentOrder($row, $fulfillmentOrderId, $orderId), $fulfillment_orders);
    }

    private function transformFulfillmentOrder(?array $row = null, ?string $fulfillmentOrderId = null, ?string $orderId = null)
    {
        if (! $row) {
            return $row;
        }

        $row['shop_id'] = null;
        $row['order_id'] = $orderId;
        $row['assigned_location_id'] = null;
        $row['request_status'] = strtolower($row['request_status']);
        $row['status'] = strtolower($row['status']);
        $row['supported_actions'] = array_map(fn ($action) => strtolower($action['action']), $row['supported_actions']);

        $row['line_items'] = array_map(function ($line_item) use ($fulfillmentOrderId) {
            $line_item = $line_item + [
                'fulfillment_order_id' => $fulfillmentOrderId,
                'shop_id' => null,
                'quantity' => $line_item['total_quantity'],
                'line_item_id' => $line_item['line_item']['id'],
                'fulfillable_quantity' => $line_item['remaining_quantity'],
                'variant_id' => $line_item['line_item']['variant']['id'],
            ];

            return $line_item;
        }, $row['line_items']);

        return $row;
    }
}
