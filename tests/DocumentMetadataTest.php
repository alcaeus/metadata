<?php

declare(strict_types=1);

namespace Alcaeus\Metadata\Tests;

use Alcaeus\Metadata\DocumentMetadata;
use Alcaeus\Metadata\FieldMetadata;
use Alcaeus\Metadata\Metadata;
use Alcaeus\Metadata\Tests\Fixtures\TestDocumentA;
use Alcaeus\Metadata\Type\Type;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionProperty;
use stdClass;

use function array_keys;

#[CoversClass(DocumentMetadata::class)]
class DocumentMetadataTest extends TestCase
{
    private ReflectionClass $reflectionClass;
    private FieldMetadata $identifierField;
    private FieldMetadata $nameField;
    private FieldMetadata $notesField;

    protected function setUp(): void
    {
        $this->reflectionClass = new ReflectionClass(TestDocumentA::class);

        // Create identifier field metadata
        $idProperty = new ReflectionProperty(TestDocumentA::class, 'id');
        $this->identifierField = new FieldMetadata($idProperty, '_id');

        // Create name field metadata
        $nameProperty = new ReflectionProperty(TestDocumentA::class, 'name');
        $this->nameField = new FieldMetadata($nameProperty, 'fullName');

        // Create notes field metadata
        $notesProperty = new ReflectionProperty(TestDocumentA::class, 'notes');
        $this->notesField = new FieldMetadata($notesProperty, 'notes');
    }

    public function testConstructorSetsPropertiesCorrectly(): void
    {
        $fields = [
            'id' => $this->identifierField,
            'name' => $this->nameField,
            'notes' => $this->notesField,
        ];

        $documentMetadata = new DocumentMetadata(
            $this->reflectionClass,
            $this->identifierField,
            $fields,
        );

        self::assertSame($this->reflectionClass, $documentMetadata->class);
        self::assertSame($this->identifierField, $documentMetadata->identifier);
        self::assertSame($fields, $documentMetadata->fields);
    }

    public function testClassNameGetter(): void
    {
        $fields = ['id' => $this->identifierField];

        $documentMetadata = new DocumentMetadata(
            $this->reflectionClass,
            $this->identifierField,
            $fields,
        );

        self::assertSame(TestDocumentA::class, $documentMetadata->className);
    }

    public function testWithEmptyFieldsArray(): void
    {
        $fields = [];

        $documentMetadata = new DocumentMetadata(
            $this->reflectionClass,
            $this->identifierField,
            $fields,
        );

        self::assertSame($this->reflectionClass, $documentMetadata->class);
        self::assertSame($this->identifierField, $documentMetadata->identifier);
        self::assertSame([], $documentMetadata->fields);
    }

    public function testWithSingleField(): void
    {
        $fields = ['id' => $this->identifierField];

        $documentMetadata = new DocumentMetadata(
            $this->reflectionClass,
            $this->identifierField,
            $fields,
        );

        self::assertCount(1, $documentMetadata->fields);
        self::assertArrayHasKey('id', $documentMetadata->fields);
        self::assertSame($this->identifierField, $documentMetadata->fields['id']);
    }

    public function testWithMultipleFields(): void
    {
        $fields = [
            'id' => $this->identifierField,
            'name' => $this->nameField,
            'notes' => $this->notesField,
        ];

        $documentMetadata = new DocumentMetadata(
            $this->reflectionClass,
            $this->identifierField,
            $fields,
        );

        self::assertCount(3, $documentMetadata->fields);
        self::assertArrayHasKey('id', $documentMetadata->fields);
        self::assertArrayHasKey('name', $documentMetadata->fields);
        self::assertArrayHasKey('notes', $documentMetadata->fields);

        self::assertSame($this->identifierField, $documentMetadata->fields['id']);
        self::assertSame($this->nameField, $documentMetadata->fields['name']);
        self::assertSame($this->notesField, $documentMetadata->fields['notes']);
    }

    public function testIdentifierIsIncludedInFields(): void
    {
        // Common pattern: identifier field is also included in the fields array
        $fields = [
            'id' => $this->identifierField,
            'name' => $this->nameField,
        ];

        $documentMetadata = new DocumentMetadata(
            $this->reflectionClass,
            $this->identifierField,
            $fields,
        );

        // Identifier should be accessible both ways
        self::assertSame($this->identifierField, $documentMetadata->identifier);
        self::assertSame($this->identifierField, $documentMetadata->fields['id']);

        // They should be the same object reference
        self::assertTrue($documentMetadata->identifier === $documentMetadata->fields['id']);
    }

    public function testIdentifierNotInFieldsArray(): void
    {
        // Edge case: identifier field is not included in the fields array
        $fields = [
            'name' => $this->nameField,
            'notes' => $this->notesField,
        ];

        $documentMetadata = new DocumentMetadata(
            $this->reflectionClass,
            $this->identifierField,
            $fields,
        );

        self::assertSame($this->identifierField, $documentMetadata->identifier);
        self::assertCount(2, $documentMetadata->fields);
        self::assertArrayNotHasKey('id', $documentMetadata->fields);
    }

    public function testWithFieldsHavingTypes(): void
    {
        $mockType = $this->createMock(Type::class);

        // Create field with a type
        $typedProperty = new ReflectionProperty(TestDocumentA::class, 'name');
        $typedField = new FieldMetadata($typedProperty, 'fullName', $mockType);

        $fields = [
            'id' => $this->identifierField,
            'name' => $typedField,
        ];

        $documentMetadata = new DocumentMetadata(
            $this->reflectionClass,
            $this->identifierField,
            $fields,
        );

        self::assertSame($mockType, $documentMetadata->fields['name']->type);
        self::assertNull($documentMetadata->fields['id']->type);
    }

    public function testWithDifferentClassTypes(): void
    {
        // Test with different reflection classes
        $stringClass = new ReflectionClass(stdClass::class);
        $fields = ['id' => $this->identifierField];

        $documentMetadata = new DocumentMetadata(
            $stringClass,
            $this->identifierField,
            $fields,
        );

        self::assertSame(stdClass::class, $documentMetadata->className);
        self::assertSame($stringClass, $documentMetadata->class);
    }

    public function testFieldsArrayPreservesOrder(): void
    {
        $fields = [
            'name' => $this->nameField,
            'id' => $this->identifierField,
            'notes' => $this->notesField,
        ];

        $documentMetadata = new DocumentMetadata(
            $this->reflectionClass,
            $this->identifierField,
            $fields,
        );

        $fieldKeys = array_keys($documentMetadata->fields);
        self::assertSame(['name', 'id', 'notes'], $fieldKeys);
    }

    public function testFieldsArrayCanBeAssociative(): void
    {
        // Test that fields can have different keys than property names
        $fields = [
            'identifier' => $this->identifierField,  // key != property name
            'full_name' => $this->nameField,         // key != property name
            'notes' => $this->notesField,            // key != property name
        ];

        $documentMetadata = new DocumentMetadata(
            $this->reflectionClass,
            $this->identifierField,
            $fields,
        );

        self::assertArrayHasKey('identifier', $documentMetadata->fields);
        self::assertArrayHasKey('full_name', $documentMetadata->fields);
        self::assertArrayHasKey('notes', $documentMetadata->fields);

        // But the property names should still be correct
        self::assertSame('id', $documentMetadata->fields['identifier']->propertyName);
        self::assertSame('name', $documentMetadata->fields['full_name']->propertyName);
        self::assertSame('notes', $documentMetadata->fields['notes']->propertyName);
    }

    public function testReadonlyProperties(): void
    {
        $documentMetadata = new DocumentMetadata(
            $this->reflectionClass,
            $this->identifierField,
            ['id' => $this->identifierField],
        );

        // Test that readonly properties work correctly
        self::assertSame($this->reflectionClass, $documentMetadata->class);
        self::assertSame($this->identifierField, $documentMetadata->identifier);

        // Properties should be readonly - attempting to modify would cause a PHP error
        // We can't test this directly in PHPUnit without causing fatal errors
        $reflection = new ReflectionClass($documentMetadata);
        $classProperty = $reflection->getProperty('class');
        $identifierProperty = $reflection->getProperty('identifier');
        $fieldsProperty = $reflection->getProperty('fields');

        self::assertTrue($classProperty->isReadOnly());
        self::assertTrue($identifierProperty->isReadOnly());
        self::assertTrue($fieldsProperty->isReadOnly());
    }

    public function testImplementsMetadataInterface(): void
    {
        $documentMetadata = new DocumentMetadata(
            $this->reflectionClass,
            $this->identifierField,
            [],
        );

        self::assertInstanceOf(Metadata::class, $documentMetadata);
    }
}
