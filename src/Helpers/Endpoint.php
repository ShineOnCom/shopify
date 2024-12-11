<?php

namespace Dan\Shopify\Helpers;

use Dan\Shopify\DTOs\RequestArgumentDTO;
use Dan\Shopify\Exceptions\GraphQLEnabledWithMissingQueriesException;
use Dan\Shopify\Exceptions\InvalidOrMissingEndpointException;
use Dan\Shopify\Shopify;

/**
 * Class Endpoint.
 *
 * @mixin Shopify
 *
 * @property string endpoint
 * @property array ids
 */
abstract class Endpoint
{
    /** @var string[] */
    protected static $endpoints = [
        'assets',
        'assigned_fulfillment_orders',
        'customers',
        'discount_codes',
        'disputes',
        'fulfillments',
        'fulfillment_orders',
        'fulfillment_services',
        'images',
        'metafields',
        'orders',
        'price_rules',
        'products',
        'recurring_application_charges',
        'risks',
        'smart_collections',
        'themes',
        'variants',
        'webhooks',
    ];

    /** @var Shopify */
    protected $client;

    protected readonly RequestArgumentDTO $dto;

    /**
     * Endpoint constructor.
     */
    public function __construct(Shopify $client)
    {
        $this->client = $client;
    }

    /**
     * Set our endpoint by accessing it via a property.
     *
     * @param  string  $property
     * @return $this
     */
    public function __get($property)
    {
        // If we're accessing another endpoint
        if (in_array($property, static::$endpoints)) {
            $client = $this->client;

            if (empty($client->ids)) {
                throw new InvalidOrMissingEndpointException('Calling '.$property.' from '.$this->client->api.' requires an id');
            }

            $last = array_reverse($client->ids)[0] ?? null;
            array_unshift($client->queue, [$client->api, $last]);
            $client->api = $property;
            $client->ids = [];

            return $client->__get($property);
        }

        return $this->$property ?? $this->client->__get($property);
    }

    /**
     * Handle dynamic method calls into the model.
     *
     * @param  string  $method
     * @param  array  $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        if (in_array($method, static::$endpoints)) {
            if ($parameters === []) {
                throw new InvalidOrMissingEndpointException('Calling '.$method.' from '.$this->client->api.' requires an id');
            }

            $last = array_reverse($this->client->ids)[0] ?? null;
            array_unshift($this->client->queue, [$this->client->api, $last]);
            $this->client->api = $method;
            $this->client->ids = [];

            return $this->client->$method(...$parameters);
        }

        if (in_array($method, ['increment', 'decrement'])) {
            return $this->$method(...$parameters);
        }

        return $this->client->$method(...$parameters);
    }

    public function graphQLEnabled()
    {
        return false;
    }

    public function setRequestArgumentDTO(RequestArgumentDTO $dto): self
    {
        $this->dto = $dto;

        return $this;
    }

    public static function useGraphQL(string $endpoint): bool
    {
        return (int) config('shopify.endpoints.'.$endpoint) === 1;
    }

    /**
     * All classes that extends Endpoint is expected to override this method to decide if they currently support GraphQL
     * They only need to support graphQL if the consumer configures GraphQL support based on config.shopify.endpoints
     *
     * @return bool
     *
     * @throws GraphQLEnabledWithMissingQueriesException
     */
    public function ensureGraphQLSupport(): void
    {
    }

    /**
     * @return array{query: string, variables: array}
     */
    public function makeGraphQLQuery(): array
    {
        throw new GraphQLEnabledWithMissingQueriesException('Please override makeGraphQLQuery in child class');
    }
}
