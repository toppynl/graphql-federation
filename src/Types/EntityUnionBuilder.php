<?php
namespace Toppynl\GraphQLFederation\Types;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\UnionType;
use Toppynl\GraphQLFederation\EntityConfig;

class EntityUnionBuilder
{
    /**
     * @param EntityConfig[] $configs
     */
    public static function build(array $configs): UnionType
    {
        $resolvableConfigs = array_filter($configs, fn (EntityConfig $c) => $c->resolvable);

        $typeMap = [];
        foreach ($resolvableConfigs as $config) {
            $typeMap[$config->typeName] = $config->type;
        }

        return new UnionType([
            'name' => '_Entity',
            'types' => array_values($typeMap),
            'resolveType' => function (mixed $value) use ($typeMap): ?ObjectType {
                $typename = $value['__typename'] ?? null;
                return isset($typename) ? ($typeMap[$typename] ?? null) : null;
            },
        ]);
    }
}
