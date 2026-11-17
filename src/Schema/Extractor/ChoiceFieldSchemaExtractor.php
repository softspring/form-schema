<?php

declare(strict_types=1);

namespace Softspring\Component\FormSchema\Schema\Extractor;

use Softspring\Component\FormSchema\Schema\SchemaExtractor;
use Symfony\Component\Form\ChoiceList\ChoiceListInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\ResolvedFormTypeInterface;

class ChoiceFieldSchemaExtractor extends ScalarFieldSchemaExtractor
{
    public function supports(FormInterface $field): bool
    {
        for ($type = $field->getConfig()->getType(); $type instanceof ResolvedFormTypeInterface; $type = $type->getParent()) {
            if ($type->getInnerType() instanceof ChoiceType) {
                return true;
            }
        }

        return false;
    }

    public function extract(FormInterface $field, SchemaExtractor $schemaExtractor): ?array
    {
        $options = $field->getConfig()->getOptions();
        $choices = $options['choices'] ?? null;
        if (is_array($choices) && null === $options['choice_value'] && null === $options['choice_loader']
            && !array_filter($choices, static fn (mixed $value): bool => !is_scalar($value))) {
            return $this->metadataApplier->apply($this->extractChoiceField($options), $field);
        }
        $choiceList = $field->getConfig()->getAttribute('choice_list');
        $values = $choiceList instanceof ChoiceListInterface ? $choiceList->getValues() : [];
        $schema = ['type' => 'string'];
        if ([] !== $values) {
            $schema['enum'] = $values;
        }
        if ($options['multiple']) {
            $schema = ['type' => 'array', 'items' => $schema];
        }

        return $this->metadataApplier->apply($schema, $field);
    }
}
