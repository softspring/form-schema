<?php

namespace Softspring\Component\FormSchema\Schema\Extractor;

use Softspring\Component\FormSchema\Schema\SchemaExtractor;
use Symfony\Component\Form\FormInterface;

class OverrideFieldSchemaExtractor implements FieldSchemaExtractorInterface
{
    public function supports(FormInterface $field): bool
    {
        $options = $field->getConfig()->getOptions();

        return isset($options['json_schema']) || isset($options['json_scheme']);
    }

    public function extract(FormInterface $field, SchemaExtractor $schemaExtractor): ?array
    {
        $options = $field->getConfig()->getOptions();

        return $options['json_schema'] ?? $options['json_scheme'] ?? null;
    }
}
