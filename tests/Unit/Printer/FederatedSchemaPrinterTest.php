<?php
namespace Toppynl\GraphQLFederation\Tests\Unit\Printer;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Schema;
use PHPUnit\Framework\TestCase;
use Toppynl\GraphQLFederation\EntityConfig;
use Toppynl\GraphQLFederation\FederatedSchema;
use Toppynl\GraphQLFederation\Printer\FederatedSchemaPrinter;

class FederatedSchemaPrinterTest extends TestCase
{
    private FederatedSchema $fedSchema;
    private ObjectType $productType;

    protected function setUp(): void
    {
        $this->productType = new ObjectType([
            'name' => 'Product',
            'fields' => [
                'id'   => Type::string(),
                'name' => Type::string(),
            ],
        ]);

        $query = new ObjectType([
            'name'   => 'Query',
            'fields' => ['product' => $this->productType],
        ]);

        $schema = new Schema(['query' => $query]);

        $this->fedSchema = new FederatedSchema(
            $schema,
            [new EntityConfig('Product', 'id', $this->productType)],
        );
    }

    public function test_sdl_excludes_builtin_scalars(): void
    {
        $sdl = FederatedSchemaPrinter::printForService($this->fedSchema);
        $this->assertStringNotContainsString('scalar String', $sdl);
        $this->assertStringNotContainsString('scalar Boolean', $sdl);
    }

    public function test_sdl_excludes_federation_infrastructure_types(): void
    {
        $sdl = FederatedSchemaPrinter::printForService($this->fedSchema);
        $this->assertStringNotContainsString('scalar _Any', $sdl);
        $this->assertStringNotContainsString('union _Entity', $sdl);
        $this->assertStringNotContainsString('type _Service', $sdl);
        $this->assertStringNotContainsString('scalar FieldSet', $sdl);
    }

    public function test_sdl_excludes_federation_query_fields(): void
    {
        $sdl = FederatedSchemaPrinter::printForService($this->fedSchema);
        $this->assertStringNotContainsString('_entities', $sdl);
        $this->assertStringNotContainsString('_service', $sdl);
    }

    public function test_sdl_includes_link_schema_extension(): void
    {
        $sdl = FederatedSchemaPrinter::printForService($this->fedSchema);
        $this->assertStringContainsString('extend schema', $sdl);
        $this->assertStringContainsString('@link', $sdl);
        $this->assertStringContainsString('specs.apollo.dev/federation', $sdl);
    }

    public function test_sdl_includes_key_directive_on_entity_type(): void
    {
        $sdl = FederatedSchemaPrinter::printForService($this->fedSchema);
        $this->assertStringContainsString('@key(fields: "id")', $sdl);
    }

    public function test_sdl_includes_product_type_definition(): void
    {
        $sdl = FederatedSchemaPrinter::printForService($this->fedSchema);
        $this->assertStringContainsString('type Product', $sdl);
    }
}
