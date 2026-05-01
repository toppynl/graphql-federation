<?php
namespace Toppynl\GraphQLFederation;

class ReferenceResolverRegistry
{
    /** @var array<string, callable> */
    private array $resolvers = [];

    public function register(string $typeName, callable $resolver): void
    {
        $this->resolvers[$typeName] = $resolver;
    }

    public function resolve(string $typeName, array $representation): mixed
    {
        if (!isset($this->resolvers[$typeName])) {
            return null;
        }
        try {
            return ($this->resolvers[$typeName])($representation);
        } catch (\Throwable) {
            return null;
        }
    }

    public function has(string $typeName): bool
    {
        return isset($this->resolvers[$typeName]);
    }

    /** @return string[] */
    public function registeredTypeNames(): array
    {
        return array_keys($this->resolvers);
    }
}
