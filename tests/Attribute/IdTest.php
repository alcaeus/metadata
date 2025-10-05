<?php

declare(strict_types=1);

namespace Alcaeus\Metadata\Tests\Attribute;

use Alcaeus\Metadata\Attribute\Field;
use Alcaeus\Metadata\Attribute\Id;
use Alcaeus\Metadata\FieldMetadata;
use Alcaeus\Metadata\Tests\Attribute\Fixtures\TestDocument;
use Alcaeus\Metadata\Type\DateTime;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;

#[CoversClass(Id::class)]
class IdTest extends TestCase
{
    public function testIdMapping(): void
    {
        $reflectionProperty = new ReflectionProperty(TestDocument::class, 'field');
        $field = new Id();

        $metadata = $field->createMetadata($reflectionProperty);

        self::assertInstanceOf(FieldMetadata::class, $metadata);
        self::assertSame('field', $metadata->propertyName);
        self::assertSame('_id', $metadata->fieldName);
        self::assertSame(null, $metadata->type);
    }

    public function testIdFieldWithExplicitType(): void
    {
        $reflectionProperty = new ReflectionProperty(TestDocument::class, 'field');
        $field = new Field(type: new DateTime());

        $metadata = $field->createMetadata($reflectionProperty);

        self::assertInstanceOf(FieldMetadata::class, $metadata);
        self::assertInstanceOf(DateTime::class, $metadata->type);
    }
}
