<?php

namespace Dan\Shopify\DTOs;

use Dan\Shopify\Exceptions\InvalidGraphQLCallException;

final class RequestArgumentDTO
{
    public function __construct(public readonly bool $mutate = false, public $payload = null, public readonly array $queue = [], public readonly array $arguments = [])
    {

    }

    public function getResourceId()
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

        throw new InvalidGraphQLCallException();
    }
}
