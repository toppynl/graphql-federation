<?php
namespace Toppynl\GraphQLFederation\Directives;

use GraphQL\Language\DirectiveLocation;
use GraphQL\Type\Definition\Directive;
use GraphQL\Type\Definition\ListOfType;
use GraphQL\Type\Definition\NonNull;
use GraphQL\Type\Definition\Type;
use Toppynl\GraphQLFederation\Types\FieldSetScalar;

class FederationDirectives
{
    public function __construct(private readonly FieldSetScalar $fieldSet) {}

    public function key(): Directive
    {
        return new Directive([
            'name' => 'key',
            'isRepeatable' => true,
            'locations' => [DirectiveLocation::OBJECT, DirectiveLocation::IFACE],
            'args' => [
                'fields' => ['type' => new NonNull($this->fieldSet)],
                'resolvable' => ['type' => Type::boolean(), 'defaultValue' => true],
            ],
        ]);
    }

    public function external(): Directive
    {
        return new Directive([
            'name' => 'external',
            'locations' => [DirectiveLocation::OBJECT, DirectiveLocation::FIELD_DEFINITION],
        ]);
    }

    public function requires(): Directive
    {
        return new Directive([
            'name' => 'requires',
            'locations' => [DirectiveLocation::FIELD_DEFINITION],
            'args' => ['fields' => ['type' => new NonNull($this->fieldSet)]],
        ]);
    }

    public function provides(): Directive
    {
        return new Directive([
            'name' => 'provides',
            'locations' => [DirectiveLocation::FIELD_DEFINITION],
            'args' => ['fields' => ['type' => new NonNull($this->fieldSet)]],
        ]);
    }

    public function shareable(): Directive
    {
        return new Directive([
            'name' => 'shareable',
            'locations' => [DirectiveLocation::FIELD_DEFINITION, DirectiveLocation::OBJECT],
        ]);
    }

    public function inaccessible(): Directive
    {
        return new Directive([
            'name' => 'inaccessible',
            'locations' => [
                DirectiveLocation::FIELD_DEFINITION,
                DirectiveLocation::OBJECT,
                DirectiveLocation::IFACE,
                DirectiveLocation::UNION,
                DirectiveLocation::ENUM,
                DirectiveLocation::ENUM_VALUE,
                DirectiveLocation::SCALAR,
                DirectiveLocation::INPUT_OBJECT,
                DirectiveLocation::INPUT_FIELD_DEFINITION,
            ],
        ]);
    }

    public function override(): Directive
    {
        return new Directive([
            'name' => 'override',
            'locations' => [DirectiveLocation::FIELD_DEFINITION],
            'args' => [
                'from' => ['type' => new NonNull(Type::string())],
                'label' => ['type' => Type::string()],
            ],
        ]);
    }

    public function tag(): Directive
    {
        return new Directive([
            'name' => 'tag',
            'isRepeatable' => true,
            'locations' => [
                DirectiveLocation::FIELD_DEFINITION,
                DirectiveLocation::OBJECT,
                DirectiveLocation::IFACE,
                DirectiveLocation::UNION,
                DirectiveLocation::ARGUMENT_DEFINITION,
                DirectiveLocation::SCALAR,
                DirectiveLocation::ENUM,
                DirectiveLocation::ENUM_VALUE,
                DirectiveLocation::INPUT_OBJECT,
                DirectiveLocation::INPUT_FIELD_DEFINITION,
            ],
            'args' => ['name' => ['type' => new NonNull(Type::string())]],
        ]);
    }

    public function composeDirective(): Directive
    {
        return new Directive([
            'name' => 'composeDirective',
            'locations' => [DirectiveLocation::SCHEMA],
            'args' => ['name' => ['type' => new NonNull(Type::string())]],
        ]);
    }

    public function interfaceObject(): Directive
    {
        return new Directive([
            'name' => 'interfaceObject',
            'locations' => [DirectiveLocation::OBJECT],
        ]);
    }

    public function link(): Directive
    {
        return new Directive([
            'name' => 'link',
            'isRepeatable' => true,
            'locations' => [DirectiveLocation::SCHEMA],
            'args' => [
                'url' => ['type' => new NonNull(Type::string())],
                'import' => ['type' => new ListOfType(Type::string())],
                'as' => ['type' => Type::string()],
                'for' => ['type' => Type::string()],
            ],
        ]);
    }

    /** @return Directive[] */
    public function all(): array
    {
        return [
            $this->key(),
            $this->external(),
            $this->requires(),
            $this->provides(),
            $this->shareable(),
            $this->inaccessible(),
            $this->override(),
            $this->tag(),
            $this->composeDirective(),
            $this->interfaceObject(),
            $this->link(),
        ];
    }
}
