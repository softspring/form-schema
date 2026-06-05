<?php

namespace Softspring\Component\FormSchema\Schema\Extractor;

use Softspring\Component\FormSchema\Schema\SchemaExtractor;
use Softspring\Component\FormSchema\Schema\SchemaMetadataApplier;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\PercentType;
use Symfony\Component\Form\Extension\Core\Type\RangeType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormInterface;

class ScalarFieldSchemaExtractor implements FieldSchemaExtractorInterface
{
    public function __construct(
        protected SchemaMetadataApplier $metadataApplier,
    ) {
    }

    public function supports(FormInterface $field): bool
    {
        return true;
    }

    public function extract(FormInterface $field, SchemaExtractor $schemaExtractor): ?array
    {
        $options = $field->getConfig()->getOptions();
        $type = $field->getConfig()->getType()->getInnerType()::class;

        $schema = match ($type) {
            CheckboxType::class => ['type' => 'boolean'],
            IntegerType::class => ['type' => 'integer'],
            NumberType::class, MoneyType::class, PercentType::class, RangeType::class => ['type' => 'number'],
            DateType::class, BirthdayType::class => ['type' => 'string', 'format' => 'date'],
            DateTimeType::class => ['type' => 'string', 'format' => 'date-time'],
            TimeType::class => ['type' => 'string', 'format' => 'time'],
            EmailType::class => ['type' => 'string', 'format' => 'email'],
            UrlType::class => ['type' => 'string', 'format' => 'uri'],
            ChoiceType::class => $this->extractChoiceField($options),
            FileType::class => ['type' => 'string'],
            default => ['type' => 'string'],
        };

        return $this->metadataApplier->apply($schema, $field);
    }

    protected function extractChoiceField(array $options): array
    {
        $values = array_values(array_filter(
            array_map($this->normalizeChoiceValue(...), $options['choices'] ?? []),
            static fn (mixed $value): bool => null !== $value
        ));

        $valueType = $this->inferJsonType($values);

        if ($options['multiple'] ?? false) {
            $schema = [
                'type' => 'array',
                'items' => ['type' => $valueType],
            ];

            if ([] !== $values) {
                $schema['items']['enum'] = $values;
            }

            return $schema;
        }

        $schema = ['type' => $valueType];

        if ([] !== $values) {
            $schema['enum'] = $values;
        }

        return $schema;
    }

    protected function normalizeChoiceValue(mixed $value): string|int|float|bool|null
    {
        if (is_scalar($value)) {
            return $value;
        }

        if (is_object($value) && method_exists($value, '__toString')) {
            return (string) $value;
        }

        return null;
    }

    protected function inferJsonType(array $values): string
    {
        if ([] === $values) {
            return 'string';
        }

        $types = array_unique(array_map(function (mixed $value): string {
            return match (true) {
                is_bool($value) => 'boolean',
                is_int($value) => 'integer',
                is_float($value) => 'number',
                default => 'string',
            };
        }, $values));

        return 1 === count($types) ? $types[0] : 'string';
    }
}
