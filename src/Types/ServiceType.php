<?php
namespace Toppynl\GraphQLFederation\Types;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;

class ServiceType extends ObjectType
{
    public function __construct()
    {
        parent::__construct([
            'name'   => '_Service',
            'fields' => ['sdl' => ['type' => Type::nonNull(Type::string())]],
        ]);
    }
}
