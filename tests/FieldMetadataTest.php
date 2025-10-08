<?php

declare(strict_types=1);

namespace Alcaeus\Metadata\Tests;

use Alcaeus\Metadata\FieldMetadata;
use Alcaeus\Metadata\Type\Raw;
use Alcaeus\Metadata\Type\Type;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;

#[CoversClass(FieldMetadata::class)]
class FieldMetadataTest extends TestCase
{
    private Type $mockType;

    protected function setUp(): void
    {
        // Create a test class property for testing
        $this->mockType = $this->createMock(Type::class);
    }

    public function testConstructorWithoutType(): void
    {
        $reflectionProperty = new ReflectionProperty($this->getTestObject(), 'testProperty');
        $fieldMetadata = new FieldMetadata(
            $reflectionProperty,
            'test_field',
        );

        self::assertSame($reflectionProperty, $fieldMetadata->property);
        self::assertSame('test_field', $fieldMetadata->fieldName);
        self::assertInstanceOf(Raw::class, $fieldMetadata->type);
    }

    public function testPropertyNameGetter(): void
    {
        $fieldMetadata = new FieldMetadata(
            new ReflectionProperty($this->getTestObject(), 'testProperty'),
            'test_field',
        );

        self::assertSame('testProperty', $fieldMetadata->propertyName);
    }

    public function testGetDecodedValueReturnsPropertyValue(): void
    {
        $testObject = $this->getTestObject();
        $testObject->testProperty = 'test value';

        $fieldMetadata = new FieldMetadata(
            new ReflectionProperty($testObject, 'testProperty'),
            'test_field',
        );

        $result = $fieldMetadata->getDecodedValue($testObject);

        self::assertSame('test value', $result);
    }

    public function testSetDecodedValueSetsPropertyValue(): void
    {
        $testObject = $this->getTestObject();

        $fieldMetadata = new FieldMetadata(
            new ReflectionProperty($testObject, 'testProperty'),
            'test_field',
        );

        $fieldMetadata->setDecodedValue($testObject, 'new value');

        self::assertSame('new value', $testObject->testProperty);
    }

    public function testGetEncodedValueWithoutType(): void
    {
        $testObject = $this->getTestObject();
        $testObject->testProperty = 'raw value';

        $fieldMetadata = new FieldMetadata(
            new ReflectionProperty($testObject, 'testProperty'),
            'test_field',
        );

        $result = $fieldMetadata->getEncodedValue($testObject);

        self::assertSame('raw value', $result);
    }

    public function testGetEncodedValueWithType(): void
    {
        $testObject = $this->getTestObject();
        $testObject->testProperty = 'raw value';

        $this->mockType->expects(self::once())
            ->method('encode')
            ->with('raw value')
            ->willReturn('encoded value');

        $fieldMetadata = new FieldMetadata(
            new ReflectionProperty($testObject, 'testProperty'),
            'test_field',
            $this->mockType,
        );

        $result = $fieldMetadata->getEncodedValue($testObject);

        self::assertSame('encoded value', $result);
    }

    public function testSetEncodedValueWithoutType(): void
    {
        $testObject = $this->getTestObject();

        $fieldMetadata = new FieldMetadata(
            new ReflectionProperty($testObject, 'testProperty'),
            'test_field',
        );

        $fieldMetadata->setEncodedValue($testObject, 'bson value');

        self::assertSame('bson value', $testObject->testProperty);
    }

    public function testSetEncodedValueWithType(): void
    {
        $testObject = $this->getTestObject();

        $this->mockType->expects(self::once())
            ->method('decode')
            ->with('bson value')
            ->willReturn('decoded value');

        $fieldMetadata = new FieldMetadata(
            new ReflectionProperty($testObject, 'testProperty'),
            'test_field',
            $this->mockType,
        );

        $fieldMetadata->setEncodedValue($testObject, 'bson value');

        self::assertSame('decoded value', $testObject->testProperty);
    }

    public function testWithPrivateProperty(): void
    {
        $testObject = $this->getTestObjectWithPrivateProperty();
        $testObject->setPrivateProperty('private value');

        $fieldMetadata = new FieldMetadata(
            new ReflectionProperty($testObject, 'privateProperty'),
            'private_field',
        );

        // Test getting private property value
        $result = $fieldMetadata->getDecodedValue($testObject);
        self::assertSame('private value', $result);

        // Test setting private property value
        $fieldMetadata->setDecodedValue($testObject, 'new private value');
        self::assertSame('new private value', $testObject->getPrivateProperty());
    }

    private function getTestObject(): object
    {
        return new class {
            public mixed $testProperty = null;
        };
    }

    private function getTestObjectWithPrivateProperty(): object
    {
        return new class {
            private string $privateProperty = '';

            public function setPrivateProperty(string $value): void
            {
                $this->privateProperty = $value;
            }

            public function getPrivateProperty(): string
            {
                return $this->privateProperty;
            }
        };
    }
}
