<?php

declare(strict_types=1);

namespace Softspring\Component\FormSchema\Schema\Extractor;

use Softspring\Component\FormSchema\Schema\SchemaExtractor;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\ResetType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormInterface;

class ButtonFieldSchemaExtractor implements FieldSchemaExtractorInterface
{
    public function supports(FormInterface $field): bool
    {
        $type = $field->getConfig()->getType()->getInnerType()::class;

        return in_array($type, [ButtonType::class, ResetType::class, SubmitType::class], true);
    }

    public function extract(FormInterface $field, SchemaExtractor $schemaExtractor): ?array
    {
        return null;
    }
}
