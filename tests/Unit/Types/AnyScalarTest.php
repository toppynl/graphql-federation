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
}
