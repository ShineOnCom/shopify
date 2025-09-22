<?php

namespace Dan\Shopify\Helpers;

use Dan\Shopify\ArrayGraphQL;
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
            // 'privateMetafieldNamespaces', Use: https://shopify.dev/docs/apps/build/custom-data/ownership#app-data-metafields if this is ever needed
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
        $payload = $this->dto->payload->toArray();

        $payload_fixed = [
            'topic' => $payload['topic'],
            'webhook_subscription' => [
                'callback_url' => $payload['address'],
                'format' => 'JSON',
            ],
        ];

        $query = [
            'webhookSubscriptionCreate($INPUT)' => [
                'webhookSubscription' => $this->getFields(),
                'userErrors' => [
                    'field',
                    'message',
                ],
            ],
        ];

        $query = ArrayGraphQL::convert(
            $query,
            [
                '$INPUT' => 'topic: $topic, webhookSubscription: $webhookSubscription',
            ],
            'mutation webhookSubscriptionCreate($topic: WebhookSubscriptionTopic!, $webhookSubscription: WebhookSubscriptionInput!)'
        );

        var_dump(['variables' => Util::convertKeysToCamelCase($payload_fixed)]);

        return [
            'query' => $query,
            'variables' => Util::convertKeysToCamelCase($payload_fixed),
        ];
    }

    private function deleteWebhook()
    {
        if ($id = $this->dto->getResourceId()) {
            $query = [
                'webhookSubscriptionDelete($ID)' => [
                    'deletedWebhookSubscriptionId',
                    'userErrors' => [
                        'field',
                        'message',
                    ],
                ],
            ];

            $variables = [
                'id' => $this->dto->getResourceId('WebhookSubscription'),
            ];

            $query = ArrayGraphQL::convert(
                $query,
                ['$ID' => 'id: $id'],
                'mutation webhookSubscriptionDelete($id: ID!)'
            );

            return [
                'query' => $query,
                'variables' => $variables,
            ];
        }

        throw new InvalidGraphQLCallException('ID required to delete webhook');
    }

    private function getMutation(): array
    {
        if ($this->dto->payload) {
            return $this->createWebhook();
        }

        if ($this->dto->hasResourceInQueue('delete')) {
            return $this->deleteWebhook();
        }
    }
}
