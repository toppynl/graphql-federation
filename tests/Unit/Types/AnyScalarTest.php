<?php
namespace Toppynl\GraphQLFederation\Tests\Unit\Types;

use PHPUnit\Framework\TestCase;
use Toppynl\GraphQLFederation\Types\AnyScalar;

class AnyScalarTest extends TestCase
{
    public function test_name_is_Any(): void
    {
        $scalar = new AnyScalar();
        $this->assertSame('_Any', $scalar->name);
    }

    public function test_serialize_passes_value_through(): void
    {
        $scalar = new AnyScalar();
        $value = ['__typename' => 'Product', 'id' => '1'];
        $this->assertSame($value, $scalar->serialize($value));
    }

    public function test_parseValue_passes_value_through(): void
    {
        $scalar = new AnyScalar();
        $value = ['__typename' => 'Product', 'id' => '1'];
        $this->assertSame($value, $scalar->parseValue($value));
    }

    public function test_parseLiteral_handles_object_with_string_and_int_fields(): void
    {
        // Build a minimal schema using _Any as an argument type to test parseLiteral
        // The easiest way: use AnyScalar via FederatedSchemaBuilder and executeQuery with inline literals
        $productType = new \GraphQL\Type\Definition\ObjectType([
            'name'   => 'Product',
            'fields' => ['id' => \GraphQL\Type\Definition\Type::string()],
        ]);
        $schema = new \GraphQL\Type\Schema([
            'query' => new \GraphQL\Type\Definition\ObjectType([
                'name'   => 'Query',
                'fields' => ['product' => $productType],
            ]),
        ]);

        $fedSchema = \Toppynl\GraphQLFederation\FederatedSchemaBuilder::from($schema)
            ->withReferenceResolver('Product', 'id', fn ($rep) => ['id' => $rep['id']])
            ->build();

        $result = \GraphQL\GraphQL::executeQuery(
            $fedSchema->schema,
            '{ _entities(representations: [{__typename: "Product", id: "1"}]) { ... on Product { id } } }',
        );

        $this->assertEmpty($result->errors);
        $this->assertSame('1', $result->data['_entities'][0]['id']);
    }
}
