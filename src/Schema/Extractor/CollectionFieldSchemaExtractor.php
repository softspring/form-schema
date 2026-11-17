<?php

namespace Softspring\Component\FormSchema\Schema\Extractor;

use Softspring\Component\FormSchema\Schema\SchemaExtractor;
use Softspring\Component\FormSchema\Schema\SchemaMetadataApplier;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\ResolvedFormTypeInterface;

class CollectionFieldSchemaExtractor implements FieldSchemaExtractorInterface
{
    public function __construct(
        protected SchemaMetadataApplier $metadataApplier,
    ) {
    }

    public function supports(FormInterface $field): bool
    {
        for ($type = $field->getConfig()->getType(); $type instanceof ResolvedFormTypeInterface; $type = $type->getParent()) {
            if ($type->getInnerType() instanceof CollectionType) {
                return true;
            }
        }

        return false;
    }

    public function extract(FormInterface $field, SchemaExtractor $schemaExtractor): ?array
    {
        $options = $field->getConfig()->getOptions();
        $entryType = $options['entry_type'] ?? FormType::class;
        $entryOptions = $options['entry_options'] ?? [];

        $entryForm = $field->getConfig()->getAttribute('prototype');
        if (!$entryForm instanceof FormInterface) {
            $children = $field->all();
            $entryForm = [] !== $children ? reset($children) : null;
        }
        if (!$entryForm instanceof FormInterface) {
            $entryForm = $field->getConfig()->getFormFactory()->createNamed('__entry__', $entryType, null, $entryOptions);
        }

        $schema = [
            'type' => 'array',
            'items' => $schemaExtractor->extractField($entryForm),
        ];

        return $this->metadataApplier->apply($schema, $field);
    }
}
