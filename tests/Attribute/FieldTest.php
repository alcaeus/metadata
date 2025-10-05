<?php

declare(strict_types=1);

namespace Alcaeus\Metadata\Tests\Attribute;

use Alcaeus\Metadata\Attribute\Field;
use Alcaeus\Metadata\FieldMetadata;
use Alcaeus\Metadata\Tests\Attribute\Fixtures\TestDocument;
use Alcaeus\Metadata\Type\DateTime;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;

#[CoversClass(Field::class)]
class FieldTest extends TestCase
{
    public function testBasicField(): void
    {
        $reflectionProperty = new ReflectionProperty(TestDocument::class, 'field');
        $field = new Field();

        $metadata = $field->createMetadata($reflectionProperty);

        self::assertInstanceOf(FieldMetadata::class, $metadata);
        self::assertSame('field', $metadata->propertyName);
        self::assertSame('field', $metadata->fieldName);
        self::assertSame(null, $metadata->type);
    }

    public function testFieldWithDifferentFieldName(): void
    {
        $reflectionProperty = new ReflectionProperty(TestDocument::class, 'renamedField');
        $field = new Field(fieldName: 'custom_name');

        $metadata = $field->createMetadata($reflectionProperty);

        self::assertInstanceOf(FieldMetadata::class, $metadata);
        self::assertSame('renamedField', $metadata->propertyName);
        self::assertSame('custom_name', $metadata->fieldName);
        self::assertSame(null, $metadata->type);
    }

    public function testFieldWithExplicitType(): void
    {
        $reflectionProperty = new ReflectionProperty(TestDocument::class, 'typedDateTimeImmutableField');
        $field = new Field(type: new DateTime());

        $metadata = $field->createMetadata($reflectionProperty);

        self::assertInstanceOf(FieldMetadata::class, $metadata);
        self::assertInstanceOf(DateTime::class, $metadata->type);
    }

    public function testTypeGuessingForDateTimeField(): void
    {
        $reflectionProperty = new ReflectionProperty(TestDocument::class, 'guessedDateTimeField');
        $field = new Field();

        $metadata = $field->createMetadata($reflectionProperty);

        self::assertInstanceOf(FieldMetadata::class, $metadata);
        self::assertInstanceOf(DateTime::class, $metadata->type);
    }

    public function testTypeGuessingForNullableDateTimeField(): void
    {
        $reflectionProperty = new ReflectionProperty(TestDocument::class, 'nullableDateTimeField');
        $field = new Field();

        $metadata = $field->createMetadata($reflectionProperty);

        self::assertInstanceOf(FieldMetadata::class, $metadata);
        self::assertInstanceOf(DateTime::class, $metadata->type);
    }

    public function testTypeGuessingForUnionTypeFieldReturnsNull(): void
    {
        $reflectionProperty = new ReflectionProperty(TestDocument::class, 'unionTypedField');
        $field = new Field();

        $metadata = $field->createMetadata($reflectionProperty);

        self::assertInstanceOf(FieldMetadata::class, $metadata);
        self::assertNull($metadata->type);
    }
}
