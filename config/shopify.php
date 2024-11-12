<?php

return [
    /*
    |--------------------------------------------------------------------------
    | API Base
    |--------------------------------------------------------------------------
    |
    | Configure API version in which you wish to use in your app(s).
    | e.g. admin, admin/api/2020-07, ...
    |
    | @see https://help.shopify.com/en/api/versioning
    */

    'api_base' => env('SHOPIFY_API_BASE', 'admin/api/2020-07'),

    /*
    |--------------------------------------------------------------------------
    | Shopify Shop
    |--------------------------------------------------------------------------
    |
    | If your app is managing a single shop, you should configure it here.
    |
    | e.g. my-cool-store.myshopify.com
    */

    'shop' => env('SHOPIFY_DOMAIN', ''),

    /*
    |--------------------------------------------------------------------------
    | Shopify Token
    |--------------------------------------------------------------------------
    |
    | Use of a token implies you've already proceeding to Shopify's Oauth flow
    | and have a token in your possession to make subsequent requests. See the
    | readme.md for help getting your token.
    */

    'token' => env('SHOPIFY_TOKEN', ''),

    /*
    |--------------------------------------------------------------------------
    | Options
    |--------------------------------------------------------------------------
    |
    | log_api_request_data:
    | When enabled will log the data of every API Request to shopify
    */

    'options' => [
        'log_api_request_data' => env('SHOPIFY_OPTION_LOG_API_REQUEST', 0),
        'log_api_response_data' => env('SHOPIFY_OPTION_LOG_API_RESPONSE', 0),
        'log_deprecation_warnings' => env('SHOPIFY_OPTIONS_LOG_DEPRECATION_WARNINGS', 1),
    ],

    'webhooks' => [
        /**
         * Do not forget to add 'webhook/*' to your VerifyCsrfToken middleware.
         */
        'enabled' => env('SHOPIFY_WEBHOOKS_ENABLED', 1),
        'route_prefix' => env('SHOPIFY_WEBHOOKS_ROUTE_PREFIX', 'webhook/shopify'),
        'secret' => env('SHOPIFY_WEBHOOKS_SECRET'),
        'middleware' => Dan\Shopify\Integrations\Laravel\Http\WebhookMiddleware::class,
        'event_routing' => [
            'carts/create' => Dan\Shopify\Integrations\Laravel\Events\WebhookEvent::class,
            'carts/update' => Dan\Shopify\Integrations\Laravel\Events\WebhookEvent::class,
            'checkouts/create' => Dan\Shopify\Integrations\Laravel\Events\WebhookEvent::class,
            'checkouts/delete' => Dan\Shopify\Integrations\Laravel\Events\WebhookEvent::class,
            'checkouts/update' => Dan\Shopify\Integrations\Laravel\Events\WebhookEvent::class,
            'collection_listings/add' => Dan\Shopify\Integrations\Laravel\Events\WebhookEvent::class,
            'collection_listings/remove' => Dan\Shopify\Integrations\Laravel\Events\WebhookEvent::class,
            'collection_listings/update' => Dan\Shopify\Integrations\Laravel\Events\WebhookEvent::class,
            'collections/create' => Dan\Shopify\Integrations\Laravel\Events\WebhookEvent::class,
            'collections/delete' => Dan\Shopify\Integrations\Laravel\Events\WebhookEvent::class,
            'collections/update' => Dan\Shopify\Integrations\Laravel\Events\WebhookEvent::class,
            'customer_groups/create' => Dan\Shopify\Integrations\Laravel\Events\WebhookEvent::class,
            'customer_groups/delete' => Dan\Shopify\Integrations\Laravel\Events\WebhookEvent::class,
            'customer_groups/update' => Dan\Shopify\Integrations\Laravel\Events\WebhookEvent::class,
            'customers/create' => Dan\Shopify\Integrations\Laravel\Events\WebhookEvent::class,
            'customers/delete' => Dan\Shopify\Integrations\Laravel\Events\WebhookEvent::class,
            'customers/disable' => Dan\Shopify\Integrations\Laravel\Events\WebhookEvent::class,
            'customers/enable' => Dan\Shopify\Integrations\Laravel\Events\WebhookEvent::class,
            'customers/update' => Dan\Shopify\Integrations\Laravel\Events\WebhookEvent::class,
            'disputes/create' => Dan\Shopify\Integrations\Laravel\Events\WebhookEvent::class,
            'disputes/update' => Dan\Shopify\Integrations\Laravel\Events\WebhookEvent::class,
            'draft_orders/create' => Dan\Shopify\Integrations\Laravel\Events\WebhookEvent::class,
            'draft_orders/delete' => Dan\Shopify\Integrations\Laravel\Events\WebhookEvent::class,
            'draft_orders/update' => Dan\Shopify\Integrations\Laravel\Events\WebhookEvent::class,
            'fulfillment_events/create' => Dan\Shopify\Integrations\Laravel\Events\WebhookEvent::class,
            'fulfillment_events/delete' => Dan\Shopify\Integrations\Laravel\Events\WebhookEvent::class,
            'fulfillments/create' => Dan\Shopify\Integrations\Laravel\Events\WebhookEvent::class,
            'fulfillments/update' => Dan\Shopify\Integrations\Laravel\Events\WebhookEvent::class,
            'order_transactions/create' => Dan\Shopify\Integrations\Laravel\Events\WebhookEvent::class,
            'orders/cancelled' => Dan\Shopify\Integrations\Laravel\Events\WebhookEvent::class,
            'orders/create' => Dan\Shopify\Integrations\Laravel\Events\WebhookEvent::class,
            'orders/delete' => Dan\Shopify\Integrations\Laravel\Events\WebhookEvent::class,
            'orders/fulfilled' => Dan\Shopify\Integrations\Laravel\Events\WebhookEvent::class,
            'orders/paid' => Dan\Shopify\Integrations\Laravel\Events\WebhookEvent::class,
            'orders/partially_fulfilled' => Dan\Shopify\Integrations\Laravel\Events\WebhookEvent::class,
            'orders/updated' => Dan\Shopify\Integrations\Laravel\Events\WebhookEvent::class,
            'product_listings/add' => Dan\Shopify\Integrations\Laravel\Events\WebhookEvent::class,
            'product_listings/remove' => Dan\Shopify\Integrations\Laravel\Events\WebhookEvent::class,
            'product_listings/update' => Dan\Shopify\Integrations\Laravel\Events\WebhookEvent::class,
            'products/create' => Dan\Shopify\Integrations\Laravel\Events\WebhookEvent::class,
            'products/delete' => Dan\Shopify\Integrations\Laravel\Events\WebhookEvent::class,
            'products/update' => Dan\Shopify\Integrations\Laravel\Events\WebhookEvent::class,
            'refunds/create' => Dan\Shopify\Integrations\Laravel\Events\WebhookEvent::class,
            'shop/update' => Dan\Shopify\Integrations\Laravel\Events\WebhookEvent::class,
            'app/uninstalled' => Dan\Shopify\Integrations\Laravel\Events\WebhookEvent::class,
            'themes/create' => Dan\Shopify\Integrations\Laravel\Events\WebhookEvent::class,
            'themes/delete' => Dan\Shopify\Integrations\Laravel\Events\WebhookEvent::class,
            'themes/publish' => Dan\Shopify\Integrations\Laravel\Events\WebhookEvent::class,
            'themes/update' => Dan\Shopify\Integrations\Laravel\Events\WebhookEvent::class,
        ],
    ],

    'endpoints' => [
        'assets' => (int) env('SHOPIFY_ENDPOINT_ASSETS_USE_GRAPHQL', 0),
        'assigned_fulfillment_orders' => (int) env('SHOPIFY_ENDPOINT_ASSIGNED_FULFILLMENT_ORDERS_USE_GRAPHQL', 0),
        'customers' => (int) env('SHOPIFY_ENDPOINT_CUSTOMERS_USE_GRAPHQL', 0),
        'discount_codes' => (int) env('SHOPIFY_ENDPOINT_DISCOUNT_CODES_USE_GRAPHQL', 0),
        'disputes' => (int) env('SHOPIFY_ENDPOINT_DISPUTES_USE_GRAPHQL', 0),
        'fulfillments' => (int) env('SHOPIFY_ENDPOINT_FULFILLMENTS_USE_GRAPHQL', 0),
        'fulfillment_orders' => (int) env('SHOPIFY_ENDPOINT_FULFILLMENT_ORDERS_USE_GRAPHQL', 0),
        'fulfillment_services' => (int) env('SHOPIFY_ENDPOINT_FULFILLMENT_SERVICES_USE_GRAPHQL', 0),
        'images' => (int) env('SHOPIFY_ENDPOINT_IMAGES_USE_GRAPHQL', 0),
        'metafields' => (int) env('SHOPIFY_ENDPOINT_METAFIELDS_USE_GRAPHQL', 0),
        'orders' => (int) env('SHOPIFY_ENDPOINT_ORDERS_USE_GRAPHQL', 0),
        'price_rules' => (int) env('SHOPIFY_ENDPOINT_PRICE_RULES_USE_GRAPHQL', 0),
        'products' => (int) env('SHOPIFY_ENDPOINT_PRODUCTS_USE_GRAPHQL', 0),
        'recurring_application_charges' => (int) env('SHOPIFY_ENDPOINT_RECURRING_APPLICATION_CHARGES_USE_GRAPHQL', 0),
        'risks' => (int) env('SHOPIFY_ENDPOINT_RISKS_USE_GRAPHQL', 0),
        'smart_collections' => (int) env('SHOPIFY_ENDPOINT_SMART_COLLECTIONS_USE_GRAPHQL', 0),
        'themes' => (int) env('SHOPIFY_ENDPOINT_THEMES_USE_GRAPHQL', 0),
        'variants' => (int) env('SHOPIFY_ENDPOINT_VARIANTS_USE_GRAPHQL', 0),
        'webhooks' => (int) env('SHOPIFY_ENDPOINT_WEBHOOKS_USE_GRAPHQL', 0),
    ],
];
