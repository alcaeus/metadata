<?php

declare(strict_types=1);

namespace Alcaeus\Tests\Metadata\Loader;

use Alcaeus\Metadata\DocumentMetadata;
use Alcaeus\Metadata\DocumentMetadataStore;
use Alcaeus\Metadata\Exception\Loader\UnmappedClass;
use Alcaeus\Metadata\FieldMetadata;
use Alcaeus\Metadata\Loader\AttributeLoader;
use Alcaeus\Tests\Metadata\Fixtures\TestDocumentA;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(AttributeLoader::class)]
class AttributeLoaderTest extends TestCase
{
    private AttributeLoader $loader;
    private DocumentMetadataStore $store;

    protected function setUp(): void
    {
        $this->loader = new AttributeLoader();
        $this->store = new DocumentMetadataStore($this->loader);
    }

    public function testLoadMetadataForUnmappedClass(): void
    {
        $this->expectExceptionObject(new UnmappedClass(self::class));

        $this->loader->load(self::class, $this->store);
    }

    public function testLoadMetadata(): void
    {
        $metadata = $this->loader->load(TestDocumentA::class, $this->store);

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
