<?php
namespace Toppynl\GraphQLFederation\Types;

use GraphQL\Type\Definition\CustomScalarType;

class AnyScalar extends CustomScalarType
{
    public function __construct()
    {
        parent::__construct([
            'name' => '_Any',
            'serialize' => fn (mixed $value): mixed => $value,
            'parseValue' => fn (mixed $value): mixed => $value,
            'parseLiteral' => fn (mixed $ast): mixed => $ast->value ?? null,
        ]);
    }
}
