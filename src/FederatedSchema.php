<?php
namespace Toppynl\GraphQLFederation;

use GraphQL\Type\Schema;

class FederatedSchema
{
    /** @param EntityConfig[] $entityConfigs */
    public function __construct(
        public readonly Schema $schema,
        public readonly array $entityConfigs = [],
    ) {}
}
