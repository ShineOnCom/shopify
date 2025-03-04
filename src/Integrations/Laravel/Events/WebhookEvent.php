<?php

namespace Dan\Shopify\Integrations\Laravel\Events;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * Class WebhookEvent.
 */
class WebhookEvent implements ShouldQueue
{
    use Queueable;

    /** @var string */
    protected $topic;

    /** @var array */
    protected $data;

    /** @var string|null */
    protected $shop;

    /**
     * WebhookEvent constructor.
     *
     * @param  string  $topic
     */
    public function __construct($topic, array $data, string $shop)
    {
        $this->topic = $topic;
        $this->data = $data;
        $this->shop = $shop;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @return string
     */
    public function getTopic()
    {
        return $this->topic;
    }

    /**
     * @return string|null
     */
    public function getShop()
    {
        return $this->shop;
    }
}
