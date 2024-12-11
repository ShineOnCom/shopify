<?php

namespace Dan\Shopify\DTOs;

use Dan\Shopify\Exceptions\InvalidGraphQLCallException;
use Dan\Shopify\Util;

final class RequestArgumentDTO
{
    public function __construct(public readonly bool $mutate = false, public $payload = null, public readonly array $queue = [], public readonly array $arguments = [])
    {

    }

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
            if (is_string($this->payload)) {
                return $this->payload;
            }

            if (is_array($this->payload) && isset($this->payload['id'])) {
                return $this->payload['id'];
            }
        }

        if (! empty($this->arguments)) {
            return $this->arguments[0];
        }

        throw new InvalidGraphQLCallException('Resource ID not present');
    }

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
}
