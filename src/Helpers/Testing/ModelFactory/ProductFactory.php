<?php

namespace Dan\Shopify\Helpers\Testing\ModelFactory;

class ProductFactory
{
    /**
     * @return array
     */
    public static function create(int $quantity = 1, array $overrides = [])
    {
        if ($quantity == 1) {
            $key = 'product';
            $data = static::getSample($overrides);
        } else {
            $key = 'products';
            $data = array_fill(0, $quantity, static::getSample($overrides));
        }

        return [$key => $data];
    }

    /**
     * Example response from https://help.shopify.com/en/api/reference/products/product#show.
     *
     *
     * @return array
     */
    private static function getSample($overrides = [])
    {
        return array_merge(json_decode('{ "id": 632910392, "title": "IPod Nano - 8GB", "body_html": "<p>It\'s the small iPod with one very big idea: Video . Now the world\'s most popular music player, available in 4GB and 8GB models, lets you enjoy TV shows, movies, video podcasts, and more. The larger, brighter display means amazing picture quality. In six eye-catching colors, iPod nano is stunning all around. And with models starting at just $149, little speaks volumes.</p>", "vendor": "Apple", "product_type": "Cult Products", "created_at": "2018-09-25T15:15:37-04:00", "handle": "ipod-nano", "updated_at": "2018-09-25T15:15:37-04:00", "published_at": "2007-12-31T19:00:00-05:00", "template_suffix": null, "tags": "Emotive, Flash Memory, MP3, Music", "published_scope": "web", "admin_graphql_api_id": "gid://shopify/Product/632910392", "variants": [ { "id": 808950810, "product_id": 632910392, "title": "Pink", "price": "199.00", "sku": "IPOD2008PINK", "position": 1, "inventory_policy": "continue", "compare_at_price": null, "fulfillment_service": "manual", "inventory_management": "shopify", "option1": "Pink", "option2": null, "option3": null, "created_at": "2018-09-25T15:15:37-04:00", "updated_at": "2018-09-25T15:15:37-04:00", "taxable": true, "barcode": "1234_pink", "grams": 567, "image_id": 562641783, "inventory_quantity": 10, "weight": 1.25, "weight_unit": "lb", "inventory_item_id": 808950810, "old_inventory_quantity": 10, "requires_shipping": true, "admin_graphql_api_id": "gid://shopify/ProductVariant/808950810" }, { "id": 49148385, "product_id": 632910392, "title": "Red", "price": "199.00", "sku": "IPOD2008RED", "position": 2, "inventory_policy": "continue", "compare_at_price": null, "fulfillment_service": "manual", "inventory_management": "shopify", "option1": "Red", "option2": null, "option3": null, "created_at": "2018-09-25T15:15:37-04:00", "updated_at": "2018-09-25T15:15:37-04:00", "taxable": true, "barcode": "1234_red", "grams": 567, "image_id": null, "inventory_quantity": 20, "weight": 1.25, "weight_unit": "lb", "inventory_item_id": 49148385, "old_inventory_quantity": 20, "requires_shipping": true, "admin_graphql_api_id": "gid://shopify/ProductVariant/49148385" }, { "id": 39072856, "product_id": 632910392, "title": "Green", "price": "199.00", "sku": "IPOD2008GREEN", "position": 3, "inventory_policy": "continue", "compare_at_price": null, "fulfillment_service": "manual", "inventory_management": "shopify", "option1": "Green", "option2": null, "option3": null, "created_at": "2018-09-25T15:15:37-04:00", "updated_at": "2018-09-25T15:15:37-04:00", "taxable": true, "barcode": "1234_green", "grams": 567, "image_id": null, "inventory_quantity": 30, "weight": 1.25, "weight_unit": "lb", "inventory_item_id": 39072856, "old_inventory_quantity": 30, "requires_shipping": true, "admin_graphql_api_id": "gid://shopify/ProductVariant/39072856" }, { "id": 457924702, "product_id": 632910392, "title": "Black", "price": "199.00", "sku": "IPOD2008BLACK", "position": 4, "inventory_policy": "continue", "compare_at_price": null, "fulfillment_service": "manual", "inventory_management": "shopify", "option1": "Black", "option2": null, "option3": null, "created_at": "2018-09-25T15:15:37-04:00", "updated_at": "2018-09-25T15:15:37-04:00", "taxable": true, "barcode": "1234_black", "grams": 567, "image_id": null, "inventory_quantity": 40, "weight": 1.25, "weight_unit": "lb", "inventory_item_id": 457924702, "old_inventory_quantity": 40, "requires_shipping": true, "admin_graphql_api_id": "gid://shopify/ProductVariant/457924702" } ], "options": [ { "id": 594680422, "product_id": 632910392, "name": "Color", "position": 1, "values": [ "Pink", "Red", "Green", "Black" ] } ], "images": [ { "id": 850703190, "product_id": 632910392, "position": 1, "created_at": "2018-09-25T15:15:37-04:00", "updated_at": "2018-09-25T15:15:37-04:00", "alt": null, "width": 123, "height": 456, "src": "https://cdn.shopify.com/s/files/1/0006/9093/3842/products/ipod-nano.png?v=1537902937", "variant_ids": [], "admin_graphql_api_id": "gid://shopify/ProductImage/850703190" }, { "id": 562641783, "product_id": 632910392, "position": 2, "created_at": "2018-09-25T15:15:37-04:00", "updated_at": "2018-09-25T15:15:37-04:00", "alt": null, "width": 123, "height": 456, "src": "https://cdn.shopify.com/s/files/1/0006/9093/3842/products/ipod-nano-2.png?v=1537902937", "variant_ids": [ 808950810 ], "admin_graphql_api_id": "gid://shopify/ProductImage/562641783" } ], "image": { "id": 850703190, "product_id": 632910392, "position": 1, "created_at": "2018-09-25T15:15:37-04:00", "updated_at": "2018-09-25T15:15:37-04:00", "alt": null, "width": 123, "height": 456, "src": "https://cdn.shopify.com/s/files/1/0006/9093/3842/products/ipod-nano.png?v=1537902937", "variant_ids": [], "admin_graphql_api_id": "gid://shopify/ProductImage/850703190" } }', true), $overrides);
    }
}
