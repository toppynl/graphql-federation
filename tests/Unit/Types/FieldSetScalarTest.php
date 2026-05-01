<?php
namespace Toppynl\GraphQLFederation\Tests\Unit\Types;

use PHPUnit\Framework\TestCase;
use Toppynl\GraphQLFederation\Types\FieldSetScalar;

class FieldSetScalarTest extends TestCase
{
    public function test_name_is_FieldSet(): void
    {
        $scalar = new FieldSetScalar();
        $this->assertSame('FieldSet', $scalar->name);
    }

    public function test_serialize_casts_to_string(): void
    {
        $scalar = new FieldSetScalar();
        $this->assertSame('id', $scalar->serialize('id'));
    }

    public function test_parseValue_returns_string(): void
    {
        $scalar = new FieldSetScalar();
        $this->assertSame('id sku', $scalar->parseValue('id sku'));
    }
}
