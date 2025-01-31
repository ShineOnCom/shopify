<?php

namespace Dan\Shopify\DTOs;

use Dan\Shopify\Exceptions\InvalidGraphQLCallException;
use Dan\Shopify\Models\AbstractModel;
use Dan\Shopify\Shopify;
use Dan\Shopify\Util;
use Illuminate\Support\Arr;

final class RequestArgumentDTO
{
    public function __construct(
        public Shopify $shopify,
        public readonly bool $mutate = false,
        private $payload = null,
        private readonly array $queue = [],
        private readonly array $arguments = [],
        public ?string $append = null
    ) {}

    public function getResourceId(?string $graphQLResourceName = null)
    {
        $resourceId = $this->findResourceId();
        if ($resourceId && $graphQLResourceName) {
            return Util::toGid($resourceId, $graphQLResourceName);
        }

        return $resourceId;
    }

    private function findResourceId()
    {
        if ($this->payload) {
            if (is_string($this->payload) || is_int($this->payload)) {
                return $this->payload;
            }

            if (is_array($this->payload) && isset($this->payload['id'])) {
                return $this->payload['id'];
            }
        }

        if (! empty($this->arguments)) {
            return $this->arguments[0];
        }

        if (! empty($this->queue) && filled($this->queue[0]) && $this->queue[0][1] === null) {
            return $this->queue[0][0];
        }

        return null;
    }

    /**
     * @throws InvalidGraphQLCallException
     */
    public function findResourceIdInQueue(string $resource)
    {
        foreach ($this->queue as $row) {
            if ($row[0] === $resource) {
                return $row[1];
            }
        }

        throw new InvalidGraphQLCallException(sprintf('Resource ID for %s not found. Please call ->%s({id}) in your chain.', $resource, $resource));
    }

    public function hasResourceInQueue(string $resource): bool
    {
        foreach ($this->queue as $row) {
            if ($row[0] === $resource) {
                return true;
            }
        }

        return false;
    }

    public function getPayload($resource = null)
    {
        $payload = $this->payload instanceof AbstractModel ? $this->payload->toArray() : $this->payload;
        if ($resource && Arr::get($payload, $resource)) {
            return Arr::get($payload, $resource);
        }

        return $payload;
    }
}
