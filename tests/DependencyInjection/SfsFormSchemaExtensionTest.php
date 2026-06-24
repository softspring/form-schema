<?php

declare(strict_types=1);

namespace Softspring\Component\FormSchema\Tests\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Softspring\Component\FormSchema\DependencyInjection\SfsFormSchemaExtension;
use Softspring\Component\FormSchema\Schema\Extractor\ButtonFieldSchemaExtractor;
use Softspring\Component\FormSchema\Schema\SchemaExtractor;
use Softspring\Component\FormSchema\SfsFormSchemaBundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class SfsFormSchemaExtensionTest extends TestCase
{
    public function testLoadsSchemaServicesAndFieldExtractorTags(): void
    {
        $container = new ContainerBuilder();
        $extension = new SfsFormSchemaExtension();

        $extension->load([], $container);

        self::assertTrue($container->hasDefinition(SchemaExtractor::class));
        self::assertTrue($container->hasDefinition(ButtonFieldSchemaExtractor::class));
        self::assertArrayHasKey('softspring.form_schema.field_extractor', $container->getDefinition(ButtonFieldSchemaExtractor::class)->getTags());
    }

    public function testBundlePathPointsToPackageRoot(): void
    {
        $bundle = new SfsFormSchemaBundle();

        self::assertSame(\dirname(__DIR__, 2), $bundle->getPath());
    }
}
