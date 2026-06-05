<?php

declare(strict_types=1);

namespace Softspring\Component\FormSchema\Schema\Extractor;

use Softspring\Component\FormSchema\Schema\SchemaExtractor;
use Softspring\Component\FormSchema\Schema\SchemaMetadataApplier;
use Symfony\Component\Form\FormInterface;

class CompoundFieldSchemaExtractor implements FieldSchemaExtractorInterface
{
    public function __construct(
        protected SchemaMetadataApplier $metadataApplier,
    ) {
    }

    public function supports(FormInterface $field): bool
    {
        return $field->getConfig()->getCompound();
    }

    public function extract(FormInterface $field, SchemaExtractor $schemaExtractor): ?array
    {
        return $this->metadataApplier->apply($schemaExtractor->extractForm($field), $field);
    }
}
