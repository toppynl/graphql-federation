<?php
namespace Toppynl\GraphQLFederation;

use GraphQL\Type\Definition\ObjectType;

final class EntityConfig
{
    /**
     * @param string[] $keyFieldSets Each element is a FieldSet string, e.g. ['id'] or ['id', 'sku']
     */
    public function __construct(
        public readonly string $typeName,
        public readonly array $keyFieldSets,
        public readonly ObjectType $type,
        public readonly bool $resolvable = true,
    ) {}
}
