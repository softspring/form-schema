# Form Schema Features

Functional definition for `softspring/form-schema`.

This package is in active development. The functional scope is useful for experimentation and early integrations, but the API and schema output are not final yet.

## Purpose

`form-schema` converts Symfony form definitions into JSON-schema-like arrays that can be used by developer tools, AI integrations, documentation generators, and validation helpers.

## Main Features

- Extract object schemas from Symfony forms.
- Mark required fields from Symfony form configuration.
- Extract scalar field types such as string, integer, number, boolean, and email.
- Extract choices as enum values when choices are scalar.
- Extract collection fields as array schemas.
- Extract compound fields as nested object schemas.
- Skip button fields.
- Read explicit schema overrides through `json_schema` and legacy `json_scheme` options.
- Apply basic metadata from labels, help text, non-empty defaults, and supported validator constraints.
- Register field extractors through Symfony service tags.

## Extension Points

- Add a custom `FieldSchemaExtractorInterface` service for unsupported form types.
- Use explicit `json_schema` options for fields that cannot be inferred safely.
- Pass a resolved `FormInterface` when schema generation depends on runtime options.

## Current Limits

- The output is JSON-schema-like and not guaranteed to be a complete JSON Schema specification document.
- Not every Symfony form type has a dedicated extractor yet.
- Complex data transformers and application-specific validation rules may require explicit schema overrides.
- The package is not yet considered stable.
