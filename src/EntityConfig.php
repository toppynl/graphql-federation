<?php
namespace Toppynl\GraphQLFederation;

use GraphQL\Type\Definition\ObjectType;

final class EntityConfig
{
    public function __construct(
        public readonly string $typeName,
        public readonly string $fields,
        public readonly ObjectType $type,
        public readonly bool $resolvable = true,
    ) {}
}
