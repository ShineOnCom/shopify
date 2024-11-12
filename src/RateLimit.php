<?php

namespace Dan\Shopify;

use Illuminate\Http\Client\Response;
use JsonSerializable;

/**
 * Class RateLimit
 */
class RateLimit implements JsonSerializable
{
    public const HEADER_CALL_LIMIT = 'X-Shopify-Shop-Api-Call-Limit';

    public const HEADER_RETRY_AFTER = 'Retry-After';

    /** @var int */
    protected $calls = 0;

    /** @var int */
    protected $cap = 40;

    /** @var int */
    protected $retry_after = 0;

    /**
     * RateLimit constructor.
     */
    public function __construct(?Response $response)
    {
        if ($response) {
            $call_limit = $response->header(static::HEADER_CALL_LIMIT) ?: '0/40';

            [$this->calls, $this->cap] = explode('/', $call_limit);

            $this->retry_after = $response->header(static::HEADER_RETRY_AFTER) ?: 0;
        }
    }

    /**
     * @return bool
     */
    public function accepts()
    {
        return $this->calls < $this->cap;
    }

    /**
     * @return int
     */
    public function calls()
    {
        return $this->calls;
    }

    /**
     * @return mixed|bool
     */
    public function exceeded(?callable $exceeded = null, ?callable $remaining = null)
    {
        $state = $this->calls >= $this->cap;

        if ($state && $exceeded) {
            return $exceeded($this);
        }

        if (! $state && $remaining) {
            return $remaining($this);
        }

        return $state;
    }

    /**
     * @return mixed|bool
     */
    public function remaining(?callable $remaining = null, ?callable $exceeded = null)
    {
        $state = ($this->cap - $this->calls) > 0;

        if ($state && $remaining) {
            return $remaining($this);
        }

        if (! $state && $exceeded) {
            return $exceeded($this);
        }

        return $state;
    }

    /**
     * @return int
     */
    public function retryAfter(?callable $retry = null, ?callable $continue = null)
    {
        $state = $this->retry_after;

        if ($state && $retry) {
            sleep($state);

            return $retry($this);
        }

        if (! $state && $continue) {
            return $continue($this);
        }

        return $state;
    }

    /**
     * @param  mixed  $on_this
     * @return static|Shopify|mixed
     */
    public function wait($on_this = null)
    {
        if ($this->exceeded()) {
            sleep($this->retryAfter());
        }

        return $on_this ?: $this;
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        return [
            'calls' => $this->calls,
            'cap' => $this->cap,
            'retry_after' => $this->retry_after,
        ];
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return json_encode($this);
    }
}
