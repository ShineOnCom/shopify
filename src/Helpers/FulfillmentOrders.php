<?php

namespace Dan\Shopify\Helpers;

use Dan\Shopify\Exceptions\GraphQLEnabledWithMissingQueriesException;

class FulfillmentOrders extends Endpoint
{
    public function graphQLEnabled()
    {
        return parent::useGraphQL('fulfillment_orders');
    }

    public function ensureGraphQLSupport(): void
    {
        if ($this->graphQLEnabled()) {
            throw new GraphQLEnabledWithMissingQueriesException();
        }
    }

    /**
     * Accept a fulfillment request.
     *
     * @param  array  $payload
     * @return array|\Dan\Shopify\Models\AbstractModel
     *
     * @throws \Dan\Shopify\Exceptions\InvalidOrMissingEndpointException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function accept($payload = [])
    {
        return $this->client->post($payload, 'fulfillment_request/accept');
    }

    /**
     * Mark a fulfillment order as cancelled.
     *
     * @param  int|null  $id
     * @return array|\Dan\Shopify\Models\AbstractModel
     *
     * @throws \Dan\Shopify\Exceptions\InvalidOrMissingEndpointException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function cancel($id = null)
    {
        $path = is_null($id) ? 'cancel' : "{$id}/cancel";

        return $this->client->post([], $path);
    }

    /**
     * Marks an in progress fulfillment order as incomplete.
     *
     * @param  array  $payload
     * @return array|\Dan\Shopify\Models\AbstractModel
     *
     * @throws \Dan\Shopify\Exceptions\InvalidOrMissingEndpointException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function close($payload = [])
    {
        return $this->client->post($payload, 'close');
    }

    /**
     * Move a fulfillment order from one location to another location.
     *
     * @param  array  $payload
     * @return array|\Dan\Shopify\Models\AbstractModel
     *
     * @throws \Dan\Shopify\Exceptions\InvalidOrMissingEndpointException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function move($payload = [])
    {
        return $this->client->post($payload, 'move');
    }

    /**
     * Marks a scheduled fulfillment order as ready for fulfillment.
     *
     * @param  array  $payload
     * @return array|\Dan\Shopify\Models\AbstractModel
     *
     * @throws \Dan\Shopify\Exceptions\InvalidOrMissingEndpointException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function open($payload = [])
    {
        return $this->client->post($payload, 'open');
    }

    /**
     * Reject a fulfillment request.
     *
     * @param  array  $payload
     * @return array|\Dan\Shopify\Models\AbstractModel
     *
     * @throws \Dan\Shopify\Exceptions\InvalidOrMissingEndpointException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function reject($payload = [])
    {
        return $this->client->post($payload, 'fulfillment_request/reject');
    }

    /**
     * Release the fulfillment hold on a fulfillment order.
     *
     * @return array|\Dan\Shopify\Models\AbstractModel
     *
     * @throws \Dan\Shopify\Exceptions\InvalidOrMissingEndpointException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function release_hold()
    {
        return $this->client->post([], 'release_hold');
    }

    /**
     * Updates the fulfill_at time of a scheduled fulfillment order.
     *
     * @param  array  $payload
     * @return array|\Dan\Shopify\Models\AbstractModel
     *
     * @throws \Dan\Shopify\Exceptions\InvalidOrMissingEndpointException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function reschedule($payload = [])
    {
        return $this->client->post($payload, 'reschedule');
    }
}
