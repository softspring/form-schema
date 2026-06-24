<?php

namespace Softspring\Component\FormSchema\Tests\Schema;

use PHPUnit\Framework\TestCase;
use Softspring\Component\FormSchema\Schema\Extractor\ButtonFieldSchemaExtractor;
use Softspring\Component\FormSchema\Schema\Extractor\CollectionFieldSchemaExtractor;
use Softspring\Component\FormSchema\Schema\Extractor\CompoundFieldSchemaExtractor;
use Softspring\Component\FormSchema\Schema\Extractor\OverrideFieldSchemaExtractor;
use Softspring\Component\FormSchema\Schema\Extractor\ScalarFieldSchemaExtractor;
use Softspring\Component\FormSchema\Schema\SchemaExtractor;
use Softspring\Component\FormSchema\Schema\SchemaMetadataApplier;
use stdClass;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Forms;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\Count;
use Symfony\Component\Validator\Constraints\GreaterThan;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\LessThan;
use Symfony\Component\Validator\Constraints\LessThanOrEqual;
use Symfony\Component\Validator\Constraints\Range;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Validation;

class SchemaExtractorTest extends TestCase
{
    public function testExtractsJsonSchemaFromSymfonyFieldTypes(): void
    {
        $extractor = $this->createExtractor();
        $schema = $extractor->extract(new ExampleFormType());

        self::assertSame([
            'type' => 'object',
            'properties' => [
                'name' => ['type' => 'string'],
                'age' => ['type' => 'integer'],
                'enabled' => ['type' => 'boolean'],
                'tags' => [
                    'type' => 'array',
                    'items' => ['type' => 'string'],
                ],
                'metadata' => [
                    'type' => 'object',
                    'properties' => [
                        'code' => ['type' => 'string'],
                    ],
                    'required' => ['code'],
                ],
            ],
            'required' => ['name', 'enabled', 'metadata'],
        ], $schema);
    }

    public function testUsesJsonSchemeOptionWhenDefined(): void
    {
        $extractor = $this->createExtractor();
        $schema = $extractor->extract(new ExampleJsonSchemeFormType());

        self::assertSame([
            'type' => 'object',
            'properties' => [
                'payload' => [
                    'type' => 'object',
                    'properties' => [
                        'foo' => ['type' => 'string'],
                    ],
                    'required' => ['foo'],
                ],
            ],
            'required' => ['payload'],
        ], $schema);
    }

    public function testAcceptsResolvedFormInterfaceAndSkipsButtons(): void
    {
        $formFactory = Forms::createFormFactoryBuilder()->getFormFactory();
        $extractor = $this->createExtractor($formFactory);
        $form = $formFactory->create(ExampleButtonFormType::class);

        $schema = $extractor->extract($form);

        self::assertSame([
            'type' => 'object',
            'properties' => [
                'title' => ['type' => 'string'],
            ],
            'required' => ['title'],
        ], $schema);
    }

    public function testInfersChoiceTypesAndSupportsJsonSchemaAlias(): void
    {
        $extractor = $this->createExtractor();
        $schema = $extractor->extract(new ExampleChoiceFormType());

        self::assertSame([
            'type' => 'object',
            'properties' => [
                'status' => [
                    'type' => 'string',
                    'enum' => ['draft', 'published'],
                ],
                'priority' => [
                    'type' => 'integer',
                    'enum' => [1, 2, 3],
                ],
                'flags' => [
                    'type' => 'array',
                    'items' => [
                        'type' => 'boolean',
                        'enum' => [true, false],
                    ],
                ],
                'payload' => [
                    'type' => 'array',
                    'items' => ['type' => 'string'],
                ],
            ],
            'required' => ['status', 'priority', 'payload'],
        ], $schema);
    }

    public function testExtractsMetadataAndValidationConstraints(): void
    {
        $extractor = $this->createExtractor();
        $schema = $extractor->extract(new ExampleConstraintFormType());

        self::assertSame([
            'type' => 'object',
            'properties' => [
                'headline' => [
                    'type' => 'string',
                    'title' => 'Headline',
                    'description' => 'Used as main page title',
                    'minLength' => 10,
                    'maxLength' => 80,
                    'pattern' => '/^[A-Z].+$/',
                ],
                'score' => [
                    'type' => 'integer',
                    'minimum' => 1,
                    'maximum' => 10,
                ],
                'tags' => [
                    'type' => 'array',
                    'items' => ['type' => 'string'],
                    'minItems' => 1,
                    'maxItems' => 3,
                ],
            ],
            'required' => ['headline', 'score'],
        ], $schema);
    }

    public function testExtractsScalarFormatsDefaultsAndAdditionalConstraints(): void
    {
        $extractor = $this->createExtractor();
        $schema = $extractor->extract(ExampleScalarMetadataFormType::class);

        self::assertSame([
            'type' => 'object',
            'properties' => [
                'email' => [
                    'type' => 'string',
                    'format' => 'email',
                    'default' => 'support@example.com',
                ],
                'website' => [
                    'type' => 'string',
                    'format' => 'uri',
                ],
                'startsAt' => [
                    'type' => 'string',
                    'format' => 'date-time',
                ],
                'birthDate' => [
                    'type' => 'string',
                    'format' => 'date',
                ],
                'startsAtTime' => [
                    'type' => 'string',
                    'format' => 'time',
                ],
                'price' => [
                    'type' => 'number',
                    'exclusiveMinimum' => 0,
                    'minimum' => 1,
                    'exclusiveMaximum' => 100,
                    'maximum' => 99,
                ],
                'level' => [
                    'type' => 'integer',
                    'enum' => [1, 2, 3],
                ],
                'ignoredChoices' => [
                    'type' => 'string',
                ],
            ],
            'required' => ['email', 'website', 'startsAt', 'birthDate', 'startsAtTime', 'price', 'level', 'ignoredChoices'],
        ], $schema);
    }

    protected function createExtractor($formFactory = null): SchemaExtractor
    {
        $formFactory ??= Forms::createFormFactoryBuilder()
            ->addExtension(new ValidatorExtension(Validation::createValidator()))
            ->getFormFactory();
        $metadataApplier = new SchemaMetadataApplier();

        return new SchemaExtractor($formFactory, [
            new OverrideFieldSchemaExtractor(),
            new ButtonFieldSchemaExtractor(),
            new CollectionFieldSchemaExtractor($metadataApplier),
            new CompoundFieldSchemaExtractor($metadataApplier),
            new ScalarFieldSchemaExtractor($metadataApplier),
        ]);
    }
}

class ExampleFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class)
            ->add('age', IntegerType::class, ['required' => false])
            ->add('enabled', CheckboxType::class, ['required' => true])
            ->add('tags', CollectionType::class, [
                'entry_type' => TextType::class,
                'required' => false,
            ])
            ->add('metadata', FormType::class, [
                'data_class' => null,
            ])
        ;

        $builder->get('metadata')
            ->add('code', TextType::class)
        ;
    }
}

class ExampleJsonSchemeFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('payload', JsonSchemeTextType::class, [
            'json_scheme' => [
                'type' => 'object',
                'properties' => [
                    'foo' => ['type' => 'string'],
                ],
                'required' => ['foo'],
            ],
        ]);
    }
}

class ExampleButtonFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class)
            ->add('save', SubmitType::class);
    }
}

class ExampleChoiceFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('status', ChoiceType::class, [
                'choices' => [
                    'Draft' => 'draft',
                    'Published' => 'published',
                ],
            ])
            ->add('priority', ChoiceType::class, [
                'choices' => [
                    'Low' => 1,
                    'Normal' => 2,
                    'High' => 3,
                ],
            ])
            ->add('flags', ChoiceType::class, [
                'required' => false,
                'multiple' => true,
                'choices' => [
                    'Yes' => true,
                    'No' => false,
                ],
            ])
            ->add('payload', JsonSchemaTextType::class, [
                'json_schema' => [
                    'type' => 'array',
                    'items' => ['type' => 'string'],
                ],
            ]);
    }
}

class ExampleConstraintFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('headline', TextType::class, [
                'label' => 'Headline',
                'help' => 'Used as main page title',
                'constraints' => [
                    new Length(min: 10, max: 80),
                    new Regex('/^[A-Z].+$/'),
                ],
            ])
            ->add('score', IntegerType::class, [
                'constraints' => [
                    new Range(min: 1, max: 10),
                ],
            ])
            ->add('tags', CollectionType::class, [
                'required' => false,
                'entry_type' => TextType::class,
                'constraints' => [
                    new Count(min: 1, max: 3),
                ],
            ])
        ;
    }
}

class ExampleScalarMetadataFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'empty_data' => 'support@example.com',
            ])
            ->add('website', UrlType::class)
            ->add('startsAt', DateTimeType::class, [
                'widget' => 'single_text',
            ])
            ->add('birthDate', DateType::class, [
                'widget' => 'single_text',
            ])
            ->add('startsAtTime', TimeType::class, [
                'widget' => 'single_text',
            ])
            ->add('price', NumberType::class, [
                'constraints' => [
                    new GreaterThan(0),
                    new GreaterThanOrEqual(1),
                    new LessThan(100),
                    new LessThanOrEqual(99),
                ],
            ])
            ->add('level', IntegerType::class, [
                'constraints' => [
                    new Choice(choices: [1, 2, 3]),
                ],
            ])
            ->add('ignoredChoices', TextType::class, [
                'constraints' => [
                    new Choice(choices: [new stdClass()]),
                ],
            ])
        ;
    }
}

class JsonSchemaTextType extends AbstractType
{
    public function getParent(): string
    {
        return TextType::class;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefined('json_schema');
        $resolver->setAllowedTypes('json_schema', ['array', 'null']);
    }
}

class JsonSchemeTextType extends AbstractType
{
    public function getParent(): string
    {
        return TextType::class;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefined('json_scheme');
        $resolver->setAllowedTypes('json_scheme', ['array', 'null']);
    }
}
