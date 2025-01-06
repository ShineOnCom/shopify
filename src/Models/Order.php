<?php

namespace Dan\Shopify\Models;

use Dan\Shopify\Util;
use Illuminate\Support\Arr;

/**
 * Class Order.
 *
 * @property int $id
 * @property string $email
 * @property \Carbon\Carbon $closed_at
 * @property int $number
 * @property string $note
 * @property string $token
 * @property string $gateway
 * @property bool $test
 * @property float $total_price
 * @property float $subtotal_price
 * @property int $total_weight
 * @property float $total_tax
 * @property bool $taxes_included
 * @property string $currency
 * @property string $financial_status
 * @property bool $confirmed
 * @property float $total_discounts
 * @property float $total_line_items_price
 * @property string $cart_token
 * @property bool $buyer_accepts_marketing
 * @property string $name
 * @property string $referring_site
 * @property string $landing_site
 * @property \Carbon\Carbon $cancelled_at
 * @property string $cancelled_reason
 * @property float $total_price_usd
 * @property string $checkout_token
 * @property string $reference
 * @property string $user_id
 * @property int $location_id
 * @property string $location_
 * @property string $source_identifier
 * @property string $source_url
 * @property \Carbon\Carbon $processed_at
 * @property string $device_id
 * @property string $phone
 * @property string $customer_locale
 * @property string $app_id
 * @property string $browser_ip
 * @property string $landing_site_ref
 * @property string $order_number
 * @property array $discount_applications
 * @property array $discount_codes
 * @property array $note_attributes
 * @property array $payment_gateway_names
 * @property string $processing_method
 * @property int $checkout_id
 * @property string $source_name
 * @property string $fulfillment_status
 * @property array $tax_lines
 * @property string $tags
 * @property string $contact_email
 * @property string $order_status_url
 * @property string $admin_graphql_api_id
 * @property array $line_items
 * @property array $shipping_lines
 * @property \stdClass $billing_address
 * @property \stdClass $shipping_address
 * @property array $fulfillments
 * @property \stdClass $client_details
 * @property array $refunds
 * @property \stdClass $payment_details
 * @property \stdClass $customer
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class Order extends AbstractModel
{
    /** @var string */
    public static $resource_name = 'order';

    /** @var string */
    public static $resource_name_many = 'orders';

    /** @var array */
    protected $dates = [
        'closed_at',
        'created_at',
        'updated_at',
        'cancelled_at',
        'processed_at',
    ];

    /** @var array */
    protected $casts = [
        'test' => 'bool',
        'confirmed' => 'bool',
        'total_price' => 'float',
        'subtotal_price' => 'float',
        'total_weight' => 'float',
        'total_tax' => 'float',
        'taxes_included' => 'bool',
        'total_discounts' => 'float',
        'total_line_items_price' => 'float',
        'buyer_accepts_marketing' => 'float',
        'total_price_usd' => 'float',
        'discount_codes' => 'array',
        'note_attributes' => 'array',
        'payment_gateway_names' => 'array',
        'line_items' => 'array',
        'shipping_lines' => 'array',
        'shipping_address' => 'object',
        'billing_address' => 'object',
        'tax_lines' => 'array',
        'fulfillments' => 'array',
        'refunds' => 'array',
        'customer' => 'object',
        'client_details' => 'object',
        'payment_details' => 'object',
    ];

    // Financial statuses from Shopify
    const FINANCIAL_STATUS_AUTHORIZED = 'authorized';

    const FINANCIAL_STATUS_PAID = 'paid';

    const FINANCIAL_STATUS_PARTIALLY_PAID = 'partially_paid';

    const FINANCIAL_STATUS_PARTIALLY_REFUNDED = 'partially_refunded';

    const FINANCIAL_STATUS_PENDING = 'pending';

    const FINANCIAL_STATUS_REFUNDED = 'refunded';

    const FINANCIAL_STATUS_VOIDED = 'voided';

    /** @var array */
    public static $financial_statuses = [
        self::FINANCIAL_STATUS_AUTHORIZED,
        self::FINANCIAL_STATUS_PAID,
        self::FINANCIAL_STATUS_PARTIALLY_PAID,
        self::FINANCIAL_STATUS_PARTIALLY_REFUNDED,
        self::FINANCIAL_STATUS_PENDING,
        self::FINANCIAL_STATUS_REFUNDED,
        self::FINANCIAL_STATUS_VOIDED,
    ];

    // Fulfillment statuses from Shopify
    const FULFILLMENT_STATUS_FILLED = 'fulfilled';

    const FULFILLMENT_STATUS_PARTIAL = 'partial';

    const FULFILLMENT_STATUS_UNFILLED = null;

    /** @var array */
    public static $fulfillment_statuses = [
        self::FULFILLMENT_STATUS_FILLED,
        self::FULFILLMENT_STATUS_PARTIAL,
        self::FULFILLMENT_STATUS_UNFILLED,
    ];

    // Risk recommendations from Shopify
    const RISK_RECOMMENDATION_LOW = 'accept';

    const RISK_RECOMMENDATION_MEDIUM = 'investigate';

    const RISK_RECOMMENDATION_HIGH = 'cancel';

    /** @var array */
    public static $risk_statuses = [
        self::RISK_RECOMMENDATION_LOW,
        self::RISK_RECOMMENDATION_MEDIUM,
        self::RISK_RECOMMENDATION_HIGH,
    ];

    const FILTER_STATUS_ANY = 'any';

    const FILTER_STATUS_CANCELLED = 'cancelled';

    const FILTER_STATUS_CLOSED = 'closed';

    const FILTER_STATUS_OPEN = 'open';

    /** @var array */
    public static $filter_statuses = [
        self::FILTER_STATUS_ANY,
        self::FILTER_STATUS_CANCELLED,
        self::FILTER_STATUS_CLOSED,
        self::FILTER_STATUS_OPEN,
    ];

    public function transformGraphQLResponse(array $response): ?array
    {
        $response = Util::convertKeysToSnakeCase($response);

        if ($orders = Arr::get($response, 'data.orders')) {
            return collect($orders)->map(fn ($row) => $this->formatOrder($row))->values()->all();
        }

        $order = Arr::get($response, 'data.order', []);

        return $this->formatOrder($order);
    }

    public function formatOrder(array $row)
    {
        $row['id'] = (int) $row['id'];
        $row['total_weight'] = (int) $row['total_weight'];
        $row['tags'] = implode(',', $row['tags']);

        $row['billing_address']['id'] = (int) str_replace('?model_name=CustomerAddress', '', $row['billing_address']['id']);
        $row['billing_address']['country_code'] = $row['billing_address']['country_code_v2'];

        $row['shipping_address']['id'] = (int) str_replace('?model_name=CustomerAddress', '', $row['shipping_address']['id']);
        $row['shipping_address']['country_code'] = $row['shipping_address']['country_code_v2'];
        $row['shipping_address']['country_code'] = $row['shipping_address']['country_code_v2'];

        $row['customer']['id'] = (int) $row['customer']['id'];
        $row['customer']['admin_graphql_api_id'] = Util::toGid($row['customer']['id'], 'Customer');
        $row['customer']['currency'] = null;

        $row['customer']['default_address']['customer_id'] = $row['customer']['id'];
        $row['customer']['default_address']['id'] = (int) str_replace('?model_name=CustomerAddress', '', $row['customer']['default_address']['id']);
        $row['customer']['default_address']['country_code'] = $row['customer']['default_address']['country_code_v2'];
        $row['customer']['default_address']['country_name'] = $row['customer']['default_address']['country'];
        $row['customer']['default_address']['default'] = true;

        $row['customer']['email_marketing_consent']['opt_in_level'] = $row['customer']['email_marketing_consent']['marketing_opt_in_level'];
        $row['customer']['email_marketing_consent']['state'] = $row['customer']['email_marketing_consent']['marketing_state'];

        $row['line_items'] = array_map(function ($line_item) {
            return [
                ...$line_item,
                'admin_graphql_api_id' => Util::toGid($line_item['id'], 'LineItem'),
                'fulfillable_quantity' => (int) $line_item['quantity'] - (int) $line_item['non_fulfillable_quantity'],
                'fulfillment_service' => $line_item['fulfillment_service']['handle'],
                'id' => (int) $line_item['id'],
                'gift_card' => $line_item['is_gift_card'],
                'grams' => null,
                'price' => Arr::get($line_item, 'original_unit_price_set.shop_money.amount'),
                'price_set' => $line_item['original_unit_price_set'],
                'product_exists' => isset($line_item['product']),
                'product_id' => (int) Arr::get($line_item, 'product.id'),
                'properties' => [],
                'total_discount' => Arr::get($line_item, 'total_discount_set.shop_money.amount'),
                'variant_id' => (int) Arr::get($line_item, 'variant.id'),
                'variant_inventory_management' => null,
            ];
        }, $row['line_items']);

        $extra = [
            'admin_graphql_api_id' => Util::toGid($row['id'], 'Order'),
            'app_id' => (int) Arr::get($row['app'], 'id'),
            'browser_ip' => $row['client_ip'],
            'buyer_accepts_marketing' => $row['customer_accepts_marketing'],
            'currency' => $row['currency_code'],
            'cart_token' => null,
            'checkout_id' => null,
            'checkout_token' => null,
            'client_details' => [
                'accept_language' => null,
                'browser_height' => null,
                'browser_width' => null,
                'browser_ip' => $row['browser_ip'],
                'session_hash' => null,
                'user_agent' => null,
            ],
            'company' => null,
            'contact_email' => $row['email'],
            'current_subtotal_price' => Arr::get($row, 'current_subtotal_price_set.shop_money.amount'),
            'current_total_additional_fees_set' => Arr::get($row, 'current_total_additional_fees_set.shop_money.amount'),
            'current_total_discounts' => Arr::get($row, 'current_total_discounts_set.shop_money.amount'),
            'current_total_price' => Arr::get($row, 'current_total_price_set.shop_money.amount'),
            'current_total_tax' => Arr::get($row, 'current_total_tax_set.shop_money.amount'),
            'subtotal_price' => Arr::get($row, 'subtotal_price_set.shop_money.amount'),
            'total_discounts' => Arr::get($row, 'total_discounts_set.shop_money.amount'),
            'total_outstanding' => Arr::get($row, 'total_outstanding_set.shop_money.amount'),
            'total_price' => Arr::get($row, 'total_price_set.shop_money.amount'),
            'total_tax' => Arr::get($row, 'total_tax_set.shop_money.amount'),
            'total_tip_received' => Arr::get($row, 'total_tip_received_set.shop_money.amount'),
            'total_line_items_price' => Arr::get($row, 'subtotal_price_set.shop_money.amount'),
            'total_line_items_price_set' => $row['subtotal_price_set'],
            'device_id' => null,
            'user_id' => null,
            'landing_site' => null,
            'landing_site_ref' => null,
            'financial_status' => $row['display_financial_status'],
            'fulfillment_status' => $row['display_fulfillment_status'],
            'location_id' => null,
            'merchant_of_record_app_id' => null,
            'note_attributes' => [],
            'number' => (int) str_replace('#', '', $row['name']),
            'order_number' => (int) str_replace('#', '', $row['name']),
            'order_status_url' => $row['status_page_url'],
            'payment_terms' => null,
            'presentment_currency' => $row['presentment_currency_code'],
            'reference' => null,
            'referring_site' => null,
            'source_url' => null,
            'token' => null,
        ];

        return [...$extra, ...$row];
    }
}
