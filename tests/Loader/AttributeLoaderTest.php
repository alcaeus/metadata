<?php

declare(strict_types=1);

namespace Alcaeus\Metadata\Tests\Loader;

use Alcaeus\Metadata\Attribute\Document;
use Alcaeus\Metadata\Attribute\Field;
use Alcaeus\Metadata\DocumentMetadata;
use Alcaeus\Metadata\FieldMetadata;
use Alcaeus\Metadata\Loader\AttributeLoader;
use Alcaeus\Metadata\Metadata;
use Alcaeus\Metadata\Tests\Fixtures\TestDocumentA;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversClassesThatExtendClass;
use PHPUnit\Framework\Attributes\CoversClassesThatImplementInterface;
use PHPUnit\Framework\TestCase;

#[CoversClass(AttributeLoader::class)]
#[CoversClassesThatImplementInterface(Metadata::class)]
#[CoversClass(Document::class)]
#[CoversClassesThatExtendClass(Field::class)]
class AttributeLoaderTest extends TestCase
{
    private AttributeLoader $loader;

    protected function setUp(): void
    {
        $this->loader = new AttributeLoader();
    }

    public function testLoadMetadata(): void
    {
        $metadata = $this->loader->load(TestDocumentA::class);

        self::assertInstanceOf(DocumentMetadata::class, $metadata);
        self::assertSame(TestDocumentA::class, $metadata->className);

        self::assertInstanceOf(FieldMetadata::class, $metadata->identifier);
        self::assertSame('id', $metadata->identifier->propertyName);
        self::assertSame('_id', $metadata->identifier->fieldName);

        self::assertCount(3, $metadata->fields);

        self::assertSame($metadata->identifier, $metadata->fields['id']);

        self::assertInstanceOf(FieldMetadata::class, $metadata->fields['name']);
        self::assertSame('name', $metadata->fields['name']->propertyName);
        self::assertSame('fullName', $metadata->fields['name']->fieldName);

        self::assertInstanceOf(FieldMetadata::class, $metadata->fields['notes']);
        self::assertSame('notes', $metadata->fields['notes']->propertyName);
        self::assertSame('notes', $metadata->fields['notes']->fieldName);
    }
}
