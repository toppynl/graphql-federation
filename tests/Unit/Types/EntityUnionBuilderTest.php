<?php
namespace Toppynl\GraphQLFederation\Tests\Unit\Types;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Definition\UnionType;
use PHPUnit\Framework\TestCase;
use Toppynl\GraphQLFederation\EntityConfig;
use Toppynl\GraphQLFederation\Types\EntityUnionBuilder;

class EntityUnionBuilderTest extends TestCase
{
    public function test_builds_union_from_entity_configs(): void
    {
        $product = new ObjectType(['name' => 'Product', 'fields' => ['id' => Type::string()]]);
        $order   = new ObjectType(['name' => 'Order',   'fields' => ['id' => Type::string()]]);

        $configs = [
            new EntityConfig('Product', 'id', $product),
            new EntityConfig('Order',   'id', $order),
        ];

        $union = EntityUnionBuilder::build($configs);

        $this->assertInstanceOf(UnionType::class, $union);
        $this->assertSame('_Entity', $union->name);
        $this->assertCount(2, $union->getTypes());
    }

    public function test_resolveType_returns_correct_object_type(): void
    {
        $product = new ObjectType(['name' => 'Product', 'fields' => ['id' => Type::string()]]);
        $configs = [new EntityConfig('Product', 'id', $product)];
        $union   = EntityUnionBuilder::build($configs);

        /** @var callable $resolver */
        $resolver = $union->config['resolveType'];
        $resolved = $resolver(['__typename' => 'Product'], null, null);
        $this->assertSame($product, $resolved);
    }

    public function test_resolveType_returns_null_for_unknown_typename(): void
    {
        $product = new ObjectType(['name' => 'Product', 'fields' => ['id' => Type::string()]]);
        $configs = [new EntityConfig('Product', 'id', $product)];
        $union   = EntityUnionBuilder::build($configs);

        /** @var callable $resolver */
        $resolver = $union->config['resolveType'];
        $resolved = $resolver(['__typename' => 'Unknown'], null, null);
        $this->assertNull($resolved);
    }
}
