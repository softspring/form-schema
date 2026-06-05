<?php

namespace Softspring\Component\FormSchema\Schema;

use Softspring\Component\FormSchema\Schema\Extractor\FieldSchemaExtractorInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;

class SchemaExtractor
{
    /**
     * @param iterable<FieldSchemaExtractorInterface> $fieldExtractors
     */
    public function __construct(
        protected FormFactoryInterface $formFactory,
        protected iterable $fieldExtractors = [],
    ) {
    }

    public function extract(FormInterface|AbstractType|string $form, array $options = []): array
    {
        return $this->extractForm($this->resolveForm($form, $options));
    }

    public function extractForm(FormInterface $form): array
    {
        $schema = [
            'type' => 'object',
            'properties' => [],
        ];

        $required = [];

        foreach ($form as $name => $child) {
            $fieldSchema = $this->extractField($child);
            if (null === $fieldSchema) {
                continue;
            }

            $schema['properties'][$name] = $fieldSchema;

            if ($child->getConfig()->getRequired()) {
                $required[] = $name;
            }
        }

        if ([] !== $required) {
            $schema['required'] = $required;
        }

        return $schema;
    }

    public function extractField(FormInterface $field): ?array
    {
        foreach ($this->fieldExtractors as $fieldExtractor) {
            if ($fieldExtractor->supports($field)) {
                return $fieldExtractor->extract($field, $this);
            }
        }

        return null;
    }

    protected function resolveForm(FormInterface|AbstractType|string $form, array $options): FormInterface
    {
        if ($form instanceof FormInterface) {
            return $form;
        }

        if ($form instanceof AbstractType) {
            $form = $form::class;
        }

        return $this->formFactory->create($form, null, $options);
    }
}
