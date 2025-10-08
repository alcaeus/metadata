<?php

declare(strict_types=1);

namespace Alcaeus\Tests\Metadata\Attribute;

use Alcaeus\Metadata\Attribute\Field;
use Alcaeus\Metadata\DocumentMetadataStore;
use Alcaeus\Metadata\FieldMetadata;
use Alcaeus\Metadata\Type\DateTime;
use Alcaeus\Metadata\Type\Hash;
use Alcaeus\Metadata\Type\PackedArray;
use Alcaeus\Metadata\Type\Raw;
use Alcaeus\Metadata\Type\StringType;
use Alcaeus\Metadata\Type\Union;
use Alcaeus\Tests\Metadata\Attribute\Fixtures\TestDocument;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;

#[CoversClass(Field::class)]
class FieldTest extends TestCase
{
    private DocumentMetadataStore $store;

    protected function setUp(): void
    {
        $this->store = new DocumentMetadataStore();
    }

    public function testBasicField(): void
    {
        $reflectionProperty = new ReflectionProperty(TestDocument::class, 'field');
        $field = new Field();

        $metadata = $field->createMetadata($reflectionProperty, $this->store);

        self::assertInstanceOf(FieldMetadata::class, $metadata);
        self::assertSame('field', $metadata->propertyName);
        self::assertSame('field', $metadata->fieldName);
        self::assertInstanceOf(StringType::class, $metadata->type);
    }

    public function testFieldWithDifferentFieldName(): void
    {
        $reflectionProperty = new ReflectionProperty(TestDocument::class, 'renamedField');
        $field = new Field(fieldName: 'custom_name');

        $metadata = $field->createMetadata($reflectionProperty, $this->store);

        self::assertInstanceOf(FieldMetadata::class, $metadata);
        self::assertSame('renamedField', $metadata->propertyName);
        self::assertSame('custom_name', $metadata->fieldName);
        self::assertInstanceOf(StringType::class, $metadata->type);
    }

    public function testFieldWithExplicitType(): void
    {
        $reflectionProperty = new ReflectionProperty(TestDocument::class, 'typedDateTimeImmutableField');
        $field = new Field(type: new DateTime());

        $metadata = $field->createMetadata($reflectionProperty, $this->store);

        self::assertInstanceOf(FieldMetadata::class, $metadata);
        self::assertInstanceOf(DateTime::class, $metadata->type);
    }

    public function testTypeGuessingForDateTimeField(): void
    {
        $reflectionProperty = new ReflectionProperty(TestDocument::class, 'guessedDateTimeField');
        $field = new Field();

        $metadata = $field->createMetadata($reflectionProperty, $this->store);

        self::assertInstanceOf(FieldMetadata::class, $metadata);
        self::assertInstanceOf(DateTime::class, $metadata->type);
    }

    public function testTypeGuessingForNullableDateTimeField(): void
    {
        $reflectionProperty = new ReflectionProperty(TestDocument::class, 'nullableDateTimeField');
        $field = new Field();

        $metadata = $field->createMetadata($reflectionProperty, $this->store);

        self::assertInstanceOf(FieldMetadata::class, $metadata);
        self::assertInstanceOf(Union::class, $metadata->type);
        self::assertCount(2, $metadata->type->types);
        self::assertInstanceOf(DateTime::class, $metadata->type->types[0]);
        self::assertInstanceOf(Raw::class, $metadata->type->types[1]);
    }

    public function testTypeGuessingForUnionTypeFieldReturnsNull(): void
    {
        $reflectionProperty = new ReflectionProperty(TestDocument::class, 'unionTypedField');
        $field = new Field();

        $metadata = $field->createMetadata($reflectionProperty, $this->store);

        self::assertInstanceOf(FieldMetadata::class, $metadata);
        self::assertInstanceOf(Union::class, $metadata->type);
        self::assertCount(2, $metadata->type->types);
        self::assertInstanceOf(DateTime::class, $metadata->type->types[0]);
        self::assertInstanceOf(DateTime::class, $metadata->type->types[1]);
    }

    public function testTypeGuessingForArrayFieldWithoutPHPDoc(): void
    {
        $reflectionProperty = new ReflectionProperty(TestDocument::class, 'arrayFieldWithoutPHPDoc');
        $field = new Field();

        $metadata = $field->createMetadata($reflectionProperty, $this->store);

        self::assertInstanceOf(FieldMetadata::class, $metadata);
        self::assertInstanceOf(Hash::class, $metadata->type);
    }

    public function testTypeGuessingForArrayFieldWithPHPDocAsListOfStrings(): void
    {
        $reflectionProperty = new ReflectionProperty(TestDocument::class, 'arrayFieldWithPHPDocAsListOfStrings');
        $field = new Field();

        $metadata = $field->createMetadata($reflectionProperty, $this->store);

        self::assertInstanceOf(FieldMetadata::class, $metadata);
        self::assertInstanceOf(PackedArray::class, $metadata->type);
    }
}
