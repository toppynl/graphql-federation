# toppynl/graphql-federation

GraphQL Federation v2 subgraph support for webonyx/graphql-php.

[![CI](https://github.com/toppynl/graphql-federation/actions/workflows/ci.yml/badge.svg)](https://github.com/toppynl/graphql-federation/actions/workflows/ci.yml)

## What it does

This library decorates any `webonyx/graphql-php` `Schema` with the full set of Federation v2 plumbing — `_service`, `_entities`, the `_Entity` union, all 11 Federation v2 directives, and the correct `extend schema @link(...)` SDL header — so that a PHP service can join a WunderGraph Cosmo (or Apollo Router) federation graph as a spec-compliant subgraph. It is framework-agnostic: no Symfony, Laravel, or any other framework is required.

## Requirements

- PHP 8.1+
- `webonyx/graphql-php` ^15.0

## Installation

```bash
composer require toppynl/graphql-federation
```

## Quick start

```php
use Toppynl\GraphQLFederation\FederatedSchemaBuilder;

$fedSchema = FederatedSchemaBuilder::from($schema)
    ->withReferenceResolver('Product', 'id', function (array $rep): array {
        return ProductRepository::findById($rep['id']);
    })
    ->build();

// $fedSchema->schema is a standard webonyx Schema — pass it to GraphQL::executeQuery() as normal
$result = GraphQL::executeQuery($fedSchema->schema, $query, rootValue: null, contextValue: $context);
```

`FederatedSchemaBuilder::from()` accepts an existing `webonyx/graphql-php` `Schema`. `build()` returns a `FederatedSchema` value object whose `->schema` property is a standard `Schema` ready for execution.

## Multiple keys

Pass an array of field names to register multiple `@key` directives on a single entity:

```php
$fedSchema = FederatedSchemaBuilder::from($schema)
    ->withReferenceResolver('Product', ['id', 'sku'], function (array $rep): array {
        return isset($rep['sku'])
            ? ProductRepository::findBySku($rep['sku'])
            : ProductRepository::findById($rep['id']);
    })
    ->build();
```

## Non-resolvable stubs

Use `withEntityKey()` when this subgraph contributes fields to an entity that is owned (and resolved) by another subgraph:

```php
$fedSchema = FederatedSchemaBuilder::from($schema)
    ->withEntityKey('Order', 'id', resolvable: false)
    ->build();
```

This emits `@key(fields: "id", resolvable: false)` in the SDL without requiring a local reference resolver.

## What gets injected automatically

- `_service { sdl }` query field
- `_entities(representations: [_Any!]!)` query field
- `_Entity` union (all resolvable entity types)
- `_Any` scalar
- `FieldSet` scalar
- `_Service` type
- All 11 Federation v2 directive definitions (see below)
- `extend schema @link(url: "https://specs.apollo.dev/federation/v2.x", ...)` header in SDL

## Federation v2 directives supported

`@key`, `@external`, `@requires`, `@provides`, `@shareable`, `@inaccessible`, `@override`, `@tag`, `@composeDirective`, `@interfaceObject`, `@link`

## API Platform v3 / Symfony

For Symfony projects using API Platform v3, see [`toppynl/api-platform-federation`](https://github.com/toppynl/api-platform-federation) for a bundle that wires this library into the API Platform stack automatically.

## License

MIT
