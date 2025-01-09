<?php

namespace Dan\Shopify\Helpers;

use Dan\Shopify\ArrayGraphQL;
use Dan\Shopify\Exceptions\GraphQLEnabledWithMissingQueriesException;

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
        return $this->getWebhooks();
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
                '$PER_PAGE' => 'first: 50',
            ]),
            'variables' => null,
        ];
    }

    private function getMutation(): array
    {
        throw new GraphQLEnabledWithMissingQueriesException('Mutation not supported yet');
    }
}
