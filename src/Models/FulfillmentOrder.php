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

    public function transformGraphQLResponse(array $response): array
    {
        $response = Util::convertKeysToSnakeCase($response);
        $fulfillment_orders = Arr::get($response, 'data.order.fulfillment_orders');
        $fulfillment_order_submit_fulfillment_request = Arr::get($response, 'data.fulfillment_order_submit_fulfillment_request');
        if ($fulfillment_order_submit_fulfillment_request) {
            return $fulfillment_order_submit_fulfillment_request;
        }

        $fulfillmentOrderId = $response['id'];
        $orderId = Arr::get($response, 'data.order.id');

        return array_map(function ($row) use ($orderId, $fulfillmentOrderId) {
            $row['shop_id'] = null;
            $row['order_id'] = $orderId;
            $row['assigned_location_id'] = null;

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

        }, $fulfillment_orders);
    }
}
