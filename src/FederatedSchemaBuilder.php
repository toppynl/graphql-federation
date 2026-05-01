<?php
namespace Toppynl\GraphQLFederation;

use GraphQL\GraphQL;
use GraphQL\Type\Definition\ListOfType;
use GraphQL\Type\Definition\NonNull;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Schema;
use GraphQL\Type\SchemaConfig;
use Toppynl\GraphQLFederation\Directives\FederationDirectives;
use Toppynl\GraphQLFederation\Exception\FederationConfigException;
use Toppynl\GraphQLFederation\Printer\FederatedSchemaPrinter;
use Toppynl\GraphQLFederation\Types\AnyScalar;
use Toppynl\GraphQLFederation\Types\EntityUnionBuilder;
use Toppynl\GraphQLFederation\Types\FieldSetScalar;
use Toppynl\GraphQLFederation\Types\ServiceType;

class FederatedSchemaBuilder
{
    /** @var array<string, EntityConfig> */
    private array $entityConfigs = [];
    private ReferenceResolverRegistry $registry;

    private function __construct(private readonly Schema $schema)
    {
        $this->registry = new ReferenceResolverRegistry();
    }

    public static function from(Schema $schema): self
    {
        return new self($schema);
    }

    public function withReferenceResolver(string $typeName, string $fields, callable $resolver): self
    {
        $type = $this->schema->getType($typeName);
        if (!$type instanceof ObjectType) {
            throw new FederationConfigException(
                "Type '{$typeName}' not found in schema or is not an ObjectType.",
            );
        }
        $this->entityConfigs[$typeName] = new EntityConfig($typeName, $fields, $type);
        $this->registry->register($typeName, $resolver);
        return $this;
    }

    public function withEntityKey(string $typeName, string $fields): self
    {
        $type = $this->schema->getType($typeName);
        if (!$type instanceof ObjectType) {
            throw new FederationConfigException(
                "Type '{$typeName}' not found in schema or is not an ObjectType.",
            );
        }
        $this->entityConfigs[$typeName] = new EntityConfig($typeName, $fields, $type);
        return $this;
    }

    public function withRegistry(ReferenceResolverRegistry $registry): self
    {
        $this->registry = $registry;
        return $this;
    }

    public function build(): FederatedSchema
    {
        $this->validate();

        $fieldSet    = new FieldSetScalar();
        $any         = new AnyScalar();
        $directives  = new FederationDirectives($fieldSet);
        $serviceType = new ServiceType();

        $entityConfigs = array_values($this->entityConfigs);
        $entityUnion   = EntityUnionBuilder::build($entityConfigs);

        // We need a forward reference so the _service resolver can print the final schema.
        // Use a nullable reference variable updated after schema construction.
        $fedSchemaHolder = null;

        $registry = $this->registry;

        $originalQuery = $this->schema->getQueryType();
        $fields = $originalQuery->getFields();

        $fields['_service'] = [
            'type'    => new NonNull($serviceType),
            'resolve' => function () use (&$fedSchemaHolder): array {
                return ['sdl' => FederatedSchemaPrinter::printForService($fedSchemaHolder)];
            },
        ];

        $fields['_entities'] = [
            'type' => new NonNull(new ListOfType(Type::getNullableType($entityUnion))),
            'args' => [
                'representations' => [
                    'type' => new NonNull(new ListOfType(new NonNull($any))),
                ],
            ],
            'resolve' => function ($root, array $args) use ($registry): array {
                return array_map(function (array $rep) use ($registry): mixed {
                    $typeName = $rep['__typename'] ?? '';
                    $resolved = $registry->resolve($typeName, $rep);
                    // Ensure __typename is present on the result so the union resolveType can identify it
                    if (is_array($resolved) && !isset($resolved['__typename'])) {
                        $resolved['__typename'] = $typeName;
                    }
                    return $resolved;
                }, $args['representations']);
            },
        ];

        $newQuery = new ObjectType([
            'name'   => $originalQuery->name,
            'fields' => $fields,
        ]);

        $config = SchemaConfig::create([
            'query'      => $newQuery,
            'types'      => [$any, $fieldSet, $serviceType, $entityUnion],
            'directives' => array_merge(GraphQL::getStandardDirectives(), $directives->all()),
        ]);

        $builtSchema = new Schema($config);
        $fedSchema   = new FederatedSchema($builtSchema, $entityConfigs);

        // Populate the reference variable so the _service resolver can access it
        $fedSchemaHolder = $fedSchema;

        return $fedSchema;
    }

    private function validate(): void
    {
        foreach ($this->entityConfigs as $typeName => $config) {
            if ($config->resolvable && !$this->registry->has($typeName)) {
                throw new FederationConfigException(
                    "No reference resolver registered for federation entity '{$typeName}'. " .
                    "Call withReferenceResolver('{$typeName}', ...) or withRegistry() before build().",
                );
            }
        }
    }
}
