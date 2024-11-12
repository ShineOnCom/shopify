<?php

namespace Dan\Shopify\Test;

use Dan\Shopify\Exceptions\GraphQLEnabledWithMissingQueriesException;
use Dan\Shopify\Shopify;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Orchestra\Testbench\TestCase;
use Throwable;

class ShopApiTest extends TestCase
{
    /**
     * GET /admin/shop.json
     * Retrieves a list of products.
     *
     * @test
     *
     * @throws Throwable
     */
    public function it_gets_the_shop_data()
    {
        Http::fake(['*' => ['shop' => []]]);

        (new Shopify('shop', 'token'))->shop();

        Http::assertSent(function (Request $request) {
            return $request->url() == 'https://shop.myshopify.com/admin/shop.json'
                && $request->method() == 'GET'
                && $request->hasHeader('X-Shopify-Access-Token', 'token');
        });
    }

    public function test_throws_when_graphql_is_enabled_but_endpoint_does_not_support_graphql_yet()
    {
        config(['shopify.endpoints.assets' => 1]);
        $this->expectException(GraphQLEnabledWithMissingQueriesException::class);

        (new Shopify('shop', 'token'))->assets->get();
    }
}
