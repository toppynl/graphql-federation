<?php
namespace Toppynl\GraphQLFederation\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Toppynl\GraphQLFederation\Exception\FederationConfigException;
use Toppynl\GraphQLFederation\ReferenceResolverRegistry;

class ReferenceResolverRegistryTest extends TestCase
{
    public function test_resolves_registered_typename(): void
    {
        $registry = new ReferenceResolverRegistry();
        $registry->register('Product', fn($rep) => ['id' => $rep['id'], 'name' => 'Widget']);

        $result = $registry->resolve('Product', ['__typename' => 'Product', 'id' => '1']);
        $this->assertSame(['id' => '1', 'name' => 'Widget'], $result);
    }

    public function test_returns_null_for_unknown_typename(): void
    {
        $registry = new ReferenceResolverRegistry();
        $result = $registry->resolve('Unknown', ['__typename' => 'Unknown', 'id' => '1']);
        $this->assertNull($result);
    }

    public function test_has_returns_true_for_registered_type(): void
    {
        $registry = new ReferenceResolverRegistry();
        $registry->register('Product', fn($rep) => null);
        $this->assertTrue($registry->has('Product'));
        $this->assertFalse($registry->has('Order'));
    }

    public function test_registered_typenames_returns_all_keys(): void
    {
        $registry = new ReferenceResolverRegistry();
        $registry->register('Product', fn($rep) => null);
        $registry->register('Order', fn($rep) => null);
        $this->assertSame(['Product', 'Order'], $registry->registeredTypeNames());
    }

    public function test_resolver_exception_is_caught_and_returns_null(): void
    {
        $registry = new ReferenceResolverRegistry();
        $registry->register('Product', fn($rep) => throw new \RuntimeException('DB down'));

        $result = $registry->resolve('Product', ['__typename' => 'Product', 'id' => '1']);
        $this->assertNull($result);
    }
}
