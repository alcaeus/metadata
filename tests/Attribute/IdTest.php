<?php

declare(strict_types=1);

namespace Alcaeus\Tests\Metadata\Attribute;

use Alcaeus\Metadata\Attribute\Field;
use Alcaeus\Metadata\Attribute\Id;
use Alcaeus\Metadata\DocumentMetadataStore;
use Alcaeus\Metadata\FieldMetadata;
use Alcaeus\Metadata\Type\DateTime;
use Alcaeus\Metadata\Type\StringType;
use Alcaeus\Tests\Metadata\Attribute\Fixtures\TestDocument;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;

#[CoversClass(Id::class)]
class IdTest extends TestCase
{
    private DocumentMetadataStore $store;

    protected function setUp(): void
    {
        $this->store = new DocumentMetadataStore();
    }

    public function testIdMapping(): void
    {
        $reflectionProperty = new ReflectionProperty(TestDocument::class, 'field');
        $field = new Id();

        $metadata = $field->createMetadata($reflectionProperty, $this->store);

        self::assertInstanceOf(FieldMetadata::class, $metadata);
        self::assertSame('field', $metadata->propertyName);
        self::assertSame('_id', $metadata->fieldName);
        self::assertInstanceOf(StringType::class, $metadata->type);
    }

    public function testIdFieldWithExplicitType(): void
    {
        $reflectionProperty = new ReflectionProperty(TestDocument::class, 'field');
        $field = new Field(type: new DateTime());

        $metadata = $field->createMetadata($reflectionProperty, $this->store);

        self::assertInstanceOf(FieldMetadata::class, $metadata);
        self::assertInstanceOf(DateTime::class, $metadata->type);
    }
}
