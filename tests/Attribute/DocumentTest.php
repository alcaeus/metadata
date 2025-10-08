<?php

declare(strict_types=1);

namespace Alcaeus\Tests\Metadata\Attribute;

use Alcaeus\Metadata\Attribute\Document;
use Alcaeus\Metadata\DocumentMetadataStore;
use Alcaeus\Metadata\FieldMetadata;
use Alcaeus\Tests\Metadata\Attribute\Fixtures\DocumentWithOnlyIdentifier;
use Alcaeus\Tests\Metadata\Attribute\Fixtures\DocumentWithoutIdentifier;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use RuntimeException;

use function sprintf;

#[CoversClass(Document::class)]
class DocumentTest extends TestCase
{
    private DocumentMetadataStore $store;

    protected function setUp(): void
    {
        $this->store = new DocumentMetadataStore();
    }

    public function testLoadDocumentWithoutIdentifier(): void
    {
        $reflectionClass = new ReflectionClass(DocumentWithoutIdentifier::class);
        $attribute = $reflectionClass->getAttributes(Document::class)[0]->newInstance();
        self::assertInstanceOf(Document::class, $attribute);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(sprintf('No identifier defined for document class "%s"', DocumentWithoutIdentifier::class));

        $attribute->createMetadata($reflectionClass, $this->store);
    }

    public function testLoadDocumentWithOnlyIdentifier(): void
    {
        $reflectionClass = new ReflectionClass(DocumentWithOnlyIdentifier::class);
        $attribute = $reflectionClass->getAttributes(Document::class)[0]->newInstance();
        self::assertInstanceOf(Document::class, $attribute);

        $metadata = $attribute->createMetadata($reflectionClass, $this->store);

        self::assertInstanceOf(FieldMetadata::class, $metadata->identifier);
        self::assertSame('id', $metadata->identifier->propertyName);

        self::assertCount(1, $metadata->fields);
        self::assertSame($metadata->identifier, $metadata->fields['id']);
    }
}
