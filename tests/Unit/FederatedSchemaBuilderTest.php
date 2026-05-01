<?php
namespace Toppynl\GraphQLFederation\Tests\Unit;

use GraphQL\GraphQL;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Schema;
use PHPUnit\Framework\TestCase;
use Toppynl\GraphQLFederation\Exception\FederationConfigException;
use Toppynl\GraphQLFederation\FederatedSchemaBuilder;

class FederatedSchemaBuilderTest extends TestCase
{
    private Schema $baseSchema;
    private ObjectType $productType;

    protected function setUp(): void
    {
        $this->productType = new ObjectType([
            'name'   => 'Product',
            'fields' => ['id' => Type::string(), 'name' => Type::string()],
        ]);

        $this->baseSchema = new Schema([
            'query' => new ObjectType([
                'name'   => 'Query',
                'fields' => ['product' => $this->productType],
            ]),
        ]);
    }

    public function test_build_adds_service_query_field(): void
    {
        $fedSchema = FederatedSchemaBuilder::from($this->baseSchema)
            ->withReferenceResolver('Product', 'id', fn ($rep) => ['id' => $rep['id'], 'name' => 'Widget'])
            ->build();

        $this->assertTrue($fedSchema->schema->getQueryType()->hasField('_service'));
    }

    public function test_build_adds_entities_query_field(): void
    {
        $fedSchema = FederatedSchemaBuilder::from($this->baseSchema)
            ->withReferenceResolver('Product', 'id', fn ($rep) => ['id' => $rep['id'], 'name' => 'Widget'])
            ->build();

        $this->assertTrue($fedSchema->schema->getQueryType()->hasField('_entities'));
    }

    public function test_service_query_returns_sdl_string(): void
    {
        $fedSchema = FederatedSchemaBuilder::from($this->baseSchema)
            ->withReferenceResolver('Product', 'id', fn ($rep) => ['id' => $rep['id'], 'name' => 'Widget'])
            ->build();

        $result = GraphQL::executeQuery($fedSchema->schema, '{ _service { sdl } }');
        $this->assertEmpty($result->errors);
        $this->assertIsString($result->data['_service']['sdl']);
        $this->assertStringContainsString('@key', $result->data['_service']['sdl']);
    }

    public function test_entities_query_resolves_representations(): void
    {
        $fedSchema = FederatedSchemaBuilder::from($this->baseSchema)
            ->withReferenceResolver('Product', 'id', fn ($rep) => ['id' => $rep['id'], 'name' => 'Widget'])
            ->build();

        $result = GraphQL::executeQuery(
            $fedSchema->schema,
            '{ _entities(representations: [{__typename: "Product", id: "1"}]) { ... on Product { id name } } }',
        );

        $this->assertEmpty($result->errors);
        $this->assertSame('1', $result->data['_entities'][0]['id']);
        $this->assertSame('Widget', $result->data['_entities'][0]['name']);
    }

    public function test_throws_when_entity_key_registered_without_resolver(): void
    {
        $this->expectException(FederationConfigException::class);
        $this->expectExceptionMessageMatches('/Product/');

        FederatedSchemaBuilder::from($this->baseSchema)
            ->withEntityKey('Product', 'id')
            ->build();
    }

    public function test_build_includes_entity_config_in_federated_schema(): void
    {
        $fedSchema = FederatedSchemaBuilder::from($this->baseSchema)
            ->withReferenceResolver('Product', 'id', fn ($rep) => null)
            ->build();

        $this->assertCount(1, $fedSchema->entityConfigs);
        $this->assertSame('Product', $fedSchema->entityConfigs[0]->typeName);
    }
}
