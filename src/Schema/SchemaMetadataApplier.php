<?php

declare(strict_types=1);

namespace Softspring\Component\FormSchema\Schema;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\Count;
use Symfony\Component\Validator\Constraints\GreaterThan;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\LessThan;
use Symfony\Component\Validator\Constraints\LessThanOrEqual;
use Symfony\Component\Validator\Constraints\Range;
use Symfony\Component\Validator\Constraints\Regex;

class SchemaMetadataApplier
{
    public function apply(array $schema, FormInterface $field): array
    {
        $options = $field->getConfig()->getOptions();

        if (is_string($options['label'] ?? null)) {
            $schema['title'] = $options['label'];
        }

        if (is_string($options['help'] ?? null)) {
            $schema['description'] = $options['help'];
        }

        if (is_scalar($options['empty_data'] ?? null) && '' !== $options['empty_data']) {
            $schema['default'] = $options['empty_data'];
        }

        foreach ($options['constraints'] ?? [] as $constraint) {
            if ($constraint instanceof Constraint) {
                $schema = $this->applyConstraint($schema, $constraint);
            }
        }

        return $schema;
    }

    protected function applyConstraint(array $schema, Constraint $constraint): array
    {
        return match (true) {
            $constraint instanceof Length => $this->applyLengthConstraint($schema, $constraint),
            $constraint instanceof Count => $this->applyCountConstraint($schema, $constraint),
            $constraint instanceof Range => $this->applyRangeConstraint($schema, $constraint),
            $constraint instanceof GreaterThan => $this->applyNumericBound($schema, 'exclusiveMinimum', $constraint->value),
            $constraint instanceof GreaterThanOrEqual => $this->applyNumericBound($schema, 'minimum', $constraint->value),
            $constraint instanceof LessThan => $this->applyNumericBound($schema, 'exclusiveMaximum', $constraint->value),
            $constraint instanceof LessThanOrEqual => $this->applyNumericBound($schema, 'maximum', $constraint->value),
            $constraint instanceof Regex => $this->applyRegexConstraint($schema, $constraint),
            $constraint instanceof Choice => $this->applyChoiceConstraint($schema, $constraint),
            default => $schema,
        };
    }

    protected function applyLengthConstraint(array $schema, Length $constraint): array
    {
        if (null !== $constraint->min) {
            $schema['minLength'] = $constraint->min;
        }

        if (null !== $constraint->max) {
            $schema['maxLength'] = $constraint->max;
        }

        return $schema;
    }

    protected function applyCountConstraint(array $schema, Count $constraint): array
    {
        if (null !== $constraint->min) {
            $schema['minItems'] = $constraint->min;
        }

        if (null !== $constraint->max) {
            $schema['maxItems'] = $constraint->max;
        }

        return $schema;
    }

    protected function applyRangeConstraint(array $schema, Range $constraint): array
    {
        if (null !== $constraint->min) {
            $schema['minimum'] = $constraint->min;
        }

        if (null !== $constraint->max) {
            $schema['maximum'] = $constraint->max;
        }

        return $schema;
    }

    protected function applyNumericBound(array $schema, string $key, mixed $value): array
    {
        if (is_int($value) || is_float($value)) {
            $schema[$key] = $value;
        }

        return $schema;
    }

    protected function applyRegexConstraint(array $schema, Regex $constraint): array
    {
        if (is_string($constraint->pattern) && '' !== $constraint->pattern) {
            $schema['pattern'] = $constraint->pattern;
        }

        return $schema;
    }

    protected function applyChoiceConstraint(array $schema, Choice $constraint): array
    {
        if (!is_array($constraint->choices) || [] === $constraint->choices) {
            return $schema;
        }

        $choices = array_values(array_filter($constraint->choices, static fn (mixed $choice): bool => is_scalar($choice)));

        if ([] === $choices) {
            return $schema;
        }

        if ('array' === ($schema['type'] ?? null)) {
            $schema['items'] ??= ['type' => 'string'];
            $schema['items']['enum'] ??= $choices;

            return $schema;
        }

        $schema['enum'] ??= $choices;

        return $schema;
    }
}
