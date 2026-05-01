<?php
namespace Toppynl\GraphQLFederation\Types;

use GraphQL\Type\Definition\CustomScalarType;

class FieldSetScalar extends CustomScalarType
{
    public function __construct()
    {
        parent::__construct([
            'name' => 'FieldSet',
            'serialize' => fn (mixed $value): string => (string) $value,
            'parseValue' => fn (mixed $value): string => (string) $value,
            'parseLiteral' => fn (mixed $ast): string => (string) $ast->value,
        ]);
    }
}
