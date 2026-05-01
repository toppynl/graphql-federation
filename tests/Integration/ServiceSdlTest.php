<?php
namespace Toppynl\GraphQLFederation\Tests\Integration;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Schema;
use PHPUnit\Framework\TestCase;
use Toppynl\GraphQLFederation\FederatedSchemaBuilder;
use Toppynl\GraphQLFederation\Printer\FederatedSchemaPrinter;

class ServiceSdlTest extends TestCase
{
    public function test_emitted_sdl_contains_key_directive(): void
    {
        $product = new ObjectType([
            'name'   => 'Product',
            'fields' => ['id' => Type::string(), 'name' => Type::string()],
        ]);
        $schema = new Schema([
            'query' => new ObjectType([
                'name'   => 'Query',
                'fields' => ['product' => $product],
            ]),
        ]);

        $fedSchema = FederatedSchemaBuilder::from($schema)
            ->withReferenceResolver('Product', 'id', fn ($rep) => ['id' => $rep['id'], 'name' => 'Widget'])
            ->build();

        $sdl = FederatedSchemaPrinter::printForService($fedSchema);

        $this->assertStringContainsString('@key(fields: "id")', $sdl);
    }

    public function test_emitted_sdl_contains_link_extension(): void
    {
        $product = new ObjectType([
            'name'   => 'Product',
            'fields' => ['id' => Type::string()],
        ]);
        $schema = new Schema([
            'query' => new ObjectType([
                'name'   => 'Query',
                'fields' => ['product' => $product],
            ]),
        ]);

        $fedSchema = FederatedSchemaBuilder::from($schema)
            ->withReferenceResolver('Product', 'id', fn ($rep) => null)
            ->build();

        $sdl = FederatedSchemaPrinter::printForService($fedSchema);

        $this->assertStringContainsString('extend schema', $sdl);
        $this->assertStringContainsString('specs.apollo.dev/federation', $sdl);
    }

    public function test_sdl_does_not_contain_infrastructure_types(): void
    {
        $product = new ObjectType([
            'name'   => 'Product',
            'fields' => ['id' => Type::string()],
        ]);
        $schema = new Schema([
            'query' => new ObjectType([
                'name'   => 'Query',
                'fields' => ['product' => $product],
            ]),
        ]);

        $fedSchema = FederatedSchemaBuilder::from($schema)
            ->withReferenceResolver('Product', 'id', fn ($rep) => null)
            ->build();

        $sdl = FederatedSchemaPrinter::printForService($fedSchema);

        $this->assertStringNotContainsString('scalar _Any', $sdl);
        $this->assertStringNotContainsString('union _Entity', $sdl);
        $this->assertStringNotContainsString('type _Service', $sdl);
        $this->assertStringNotContainsString('_entities', $sdl);
        $this->assertStringNotContainsString('_service', $sdl);
    }
}
