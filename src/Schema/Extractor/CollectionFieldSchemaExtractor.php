<?php

namespace Softspring\Component\FormSchema\Schema\Extractor;

use Softspring\Component\FormSchema\Schema\SchemaExtractor;
use Softspring\Component\FormSchema\Schema\SchemaMetadataApplier;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormInterface;

class CollectionFieldSchemaExtractor implements FieldSchemaExtractorInterface
{
    public function __construct(
        protected SchemaMetadataApplier $metadataApplier,
    ) {
    }

    public function supports(FormInterface $field): bool
    {
        return CollectionType::class === $field->getConfig()->getType()->getInnerType()::class;
    }

    public function extract(FormInterface $field, SchemaExtractor $schemaExtractor): ?array
    {
        $options = $field->getConfig()->getOptions();
        $entryType = $options['entry_type'] ?? FormType::class;
        $entryOptions = $options['entry_options'] ?? [];

        $entryForm = $field->getConfig()->getFormFactory()->createNamed('__entry__', $entryType, null, $entryOptions);

        $schema = [
            'type' => 'array',
            'items' => $schemaExtractor->extractField($entryForm),
        ];

        return $this->metadataApplier->apply($schema, $field);
    }
}
