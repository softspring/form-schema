<?php

namespace Softspring\Component\FormSchema\Schema\Extractor;

use Softspring\Component\FormSchema\Schema\SchemaExtractor;
use Symfony\Component\Form\FormInterface;

interface FieldSchemaExtractorInterface
{
    public function supports(FormInterface $field): bool;

    public function extract(FormInterface $field, SchemaExtractor $schemaExtractor): ?array;
}
