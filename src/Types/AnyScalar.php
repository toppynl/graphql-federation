<?php
namespace Toppynl\GraphQLFederation\Types;

use GraphQL\Language\AST\BooleanValueNode;
use GraphQL\Language\AST\EnumValueNode;
use GraphQL\Language\AST\FloatValueNode;
use GraphQL\Language\AST\IntValueNode;
use GraphQL\Language\AST\ListValueNode;
use GraphQL\Language\AST\NullValueNode;
use GraphQL\Language\AST\ObjectValueNode;
use GraphQL\Language\AST\StringValueNode;
use GraphQL\Type\Definition\CustomScalarType;

class AnyScalar extends CustomScalarType
{
    public function __construct()
    {
        parent::__construct([
            'name'         => '_Any',
            'serialize'    => fn (mixed $value): mixed => $value,
            'parseValue'   => fn (mixed $value): mixed => $value,
            'parseLiteral' => static function (mixed $ast): mixed {
                return self::parseLiteralNode($ast);
            },
        ]);
    }

    private static function parseLiteralNode(mixed $ast): mixed
    {
        if ($ast instanceof ObjectValueNode) {
            $result = [];
            foreach ($ast->fields as $field) {
                $result[$field->name->value] = self::parseLiteralNode($field->value);
            }
            return $result;
        }

        if ($ast instanceof ListValueNode) {
            $result = [];
            foreach ($ast->values as $value) {
                $result[] = self::parseLiteralNode($value);
            }
            return $result;
        }

        if ($ast instanceof NullValueNode) {
            return null;
        }

        if ($ast instanceof BooleanValueNode) {
            return $ast->value;
        }

        if ($ast instanceof IntValueNode || $ast instanceof FloatValueNode || $ast instanceof StringValueNode || $ast instanceof EnumValueNode) {
            return $ast->value;
        }

        return null;
    }
}
