# Form Schema

[![Latest Stable](https://img.shields.io/packagist/v/softspring/form-schema?label=stable&style=flat-square)](https://github.com/softspring/form-schema/releases)
[![Latest Unstable](https://img.shields.io/packagist/v/softspring/form-schema?label=unstable&style=flat-square&include_prereleases)](https://github.com/softspring/form-schema/releases)
[![License](https://img.shields.io/packagist/l/softspring/form-schema?style=flat-square)](https://github.com/softspring/form-schema/blob/6.0/LICENSE)
[![PHP Version](https://img.shields.io/packagist/dependency-v/softspring/form-schema/php?style=flat-square)](https://github.com/softspring/form-schema/blob/6.0/composer.json)
[![Downloads](https://img.shields.io/packagist/dt/softspring/form-schema?style=flat-square)](https://packagist.org/packages/softspring/form-schema)
[![CI](https://img.shields.io/github/actions/workflow/status/softspring/form-schema/ci.yml?branch=6.0&style=flat-square&label=CI)](https://github.com/softspring/form-schema/actions/workflows/ci.yml)
[![Coverage](https://img.shields.io/codecov/c/github/softspring/form-schema?branch=6.0&style=flat-square)](https://codecov.io/gh/softspring/form-schema)

`softspring/form-schema` extracts JSON-schema-like metadata from Symfony forms.

This package is still in active development. Its public API, extracted schema shape, and extension points may change before the first stable release.

## What It Provides

- Schema extraction from a Symfony `FormInterface`, form type class, or form type instance.
- Field extractors for scalar fields, compound fields, collections, choices, and buttons.
- Support for explicit `json_schema` and `json_scheme` form options.
- Basic metadata extraction from labels, help text, defaults, and Symfony validator constraints.
- A Symfony bundle that registers the extractor services and field extractor tags.

## Installation

```bash
composer require softspring/form-schema:^6.0@dev
```

If Symfony Flex does not register the bundle automatically, add it manually:

```php
// config/bundles.php
return [
    Softspring\Component\FormSchema\SfsFormSchemaBundle::class => ['all' => true],
];
```

## Basic Usage

Inject `SchemaExtractor` and extract a schema from a form type:

```php
use Softspring\Component\FormSchema\Schema\SchemaExtractor;

final class ExampleService
{
    public function __construct(private SchemaExtractor $schemaExtractor)
    {
    }

    public function schema(): array
    {
        return $this->schemaExtractor->extract(ExampleFormType::class);
    }
}
```

You can also pass a resolved `FormInterface` when the form must be created with application-specific options.

## Custom Field Extractors

Create a service implementing `FieldSchemaExtractorInterface` and tag it with `softspring.form_schema.field_extractor`.

Extractors are evaluated in service order. Return `true` from `supports()` only for the fields your extractor owns.

## Contributing

See [CONTRIBUTING.md](CONTRIBUTING.md).

[Report issues](https://github.com/softspring/form-schema/issues) and [send Pull Requests](https://github.com/softspring/form-schema/pulls)

## Security

See [SECURITY.md](SECURITY.md).

## License

This package is free and released under the [AGPL-3.0 license](LICENSE).
