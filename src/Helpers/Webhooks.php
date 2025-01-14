<?php

namespace Dan\Shopify\Helpers;

use Dan\Shopify\ArrayGraphQL;
use Dan\Shopify\Exceptions\GraphQLEnabledWithMissingQueriesException;
use Dan\Shopify\Exceptions\InvalidGraphQLCallException;
use Dan\Shopify\Util;

/**
 * Class Webhooks.
 */
class Webhooks extends Endpoint
{
    public function graphQLEnabled()
    {
        return parent::useGraphQL('webhooks');
    }

    public function makeGraphQLQuery(): array
    {
        return $this->dto->mutate ? $this->getMutation() : $this->getQuery();
    }

    private function getFields()
    {
        return [
            'id',
            'callbackUrl',
            'topic',
            'createdAt',
            'updatedAt',
            'format',
            'includeFields',
            'metafieldNamespaces',
            'apiVersion' => [
                'displayName',
            ],
            'privateMetafieldNamespaces',
        ];
    }

    private function getQuery()
    {
        if ($this->dto->getResourceId()) {
            return $this->getWebhook();
        }

        return $this->getWebhooks();
    }

    private function getWebhook()
    {
        $fields = [
            'webhookSubscription($ID)' => $this->getFields(),
        ];

        return [
            'query' => ArrayGraphQL::convert($fields, [
                '$ID' => Util::toGraphQLIdParam($this->dto->getResourceId(), 'WebhookSubscription'),
            ]),
            'variables' => null,
        ];
    }

    private function getWebhooks()
    {
        $header = 'webhookSubscriptions($PER_PAGE)';

        $fields = [
            $header => [
                'edges' => [
                    'node' => $this->getFields(),
                ],
            ],
        ];

        return [
            'query' => ArrayGraphQL::convert($fields, [
                '$PER_PAGE' => 'first: 250',
            ]),
            'variables' => null,
        ];
    }

    private function createWebhook()
    {
        throw new InvalidGraphQLCallException('Create for Webhook not implemented');
    }

    private function deleteWebhook()
    {
        throw new InvalidGraphQLCallException('Delete for Webhook not implemented');
    }

    private function getMutation(): array
    {

        var_dump($this->dto->mutate);
        var_dump($this->dto->payload);

        return $this->dto->payload ? $this->createWebhook() : $this->deleteWebhook();
    }
}
