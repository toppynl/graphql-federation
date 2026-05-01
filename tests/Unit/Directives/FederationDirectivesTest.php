<?php
namespace Toppynl\GraphQLFederation\Tests\Unit\Directives;

use GraphQL\Language\DirectiveLocation;
use GraphQL\Type\Definition\Argument;
use PHPUnit\Framework\TestCase;
use Toppynl\GraphQLFederation\Directives\FederationDirectives;
use Toppynl\GraphQLFederation\Types\FieldSetScalar;

class FederationDirectivesTest extends TestCase
{
    private FederationDirectives $directives;

    protected function setUp(): void
    {
        $this->directives = new FederationDirectives(new FieldSetScalar());
    }

    /** @param Argument[] $args @return array<string, Argument> */
    private function argsByName(array $args): array
    {
        $map = [];
        foreach ($args as $arg) {
            $map[$arg->name] = $arg;
        }
        return $map;
    }

    public function test_key_directive_is_repeatable_on_object_and_interface(): void
    {
        $key = $this->directives->key();
        $this->assertSame('key', $key->name);
        $this->assertTrue($key->isRepeatable);
        $this->assertContains(DirectiveLocation::OBJECT, $key->locations);
        $this->assertContains(DirectiveLocation::IFACE, $key->locations);
        $args = $this->argsByName($key->args);
        $this->assertArrayHasKey('fields', $args);
        $this->assertArrayHasKey('resolvable', $args);
    }

    public function test_shareable_directive_locations(): void
    {
        $shareable = $this->directives->shareable();
        $this->assertSame('shareable', $shareable->name);
        $this->assertContains(DirectiveLocation::FIELD_DEFINITION, $shareable->locations);
        $this->assertContains(DirectiveLocation::OBJECT, $shareable->locations);
    }

    public function test_inaccessible_has_nine_locations(): void
    {
        $inaccessible = $this->directives->inaccessible();
        $this->assertCount(9, $inaccessible->locations);
    }

    public function test_override_has_from_and_label_arguments(): void
    {
        $override = $this->directives->override();
        $args = $this->argsByName($override->args);
        $this->assertArrayHasKey('from', $args);
        $this->assertArrayHasKey('label', $args);
    }

    public function test_link_is_repeatable_on_schema(): void
    {
        $link = $this->directives->link();
        $this->assertTrue($link->isRepeatable);
        $this->assertContains(DirectiveLocation::SCHEMA, $link->locations);
        $args = $this->argsByName($link->args);
        $this->assertArrayHasKey('url', $args);
        $this->assertArrayHasKey('import', $args);
    }

    public function test_all_returns_eleven_directives(): void
    {
        $all = $this->directives->all();
        $this->assertCount(11, $all);
    }
}
