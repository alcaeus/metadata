<?php

declare(strict_types=1);

namespace Alcaeus\Metadata;

use Alcaeus\Metadata\Type\Raw;
use Alcaeus\Metadata\Type\Type;
use ReflectionProperty;

/** @template T of Type */
class FieldMetadata implements Metadata
{
    // phpcs:disable
    public string $propertyName {
        get => $this->property->name;
    }
    // phpcs:enable

    /** @param T $type */
    public function __construct(
        public readonly ReflectionProperty $property,
        public readonly string $fieldName,
        public readonly Type $type = new Raw(),
    ) {
    }

    /** @return template-type<T, Type, 'NativeType'> */
    public function getDecodedValue(object $object): mixed
    {
        return $this->property->getRawValue($object);
    }

    /** @param template-type<T, Type, 'NativeType'> $value */
    public function setDecodedValue(object $object, mixed $value): void
    {
        $this->property->setRawValueWithoutLazyInitialization($object, $value);
    }

    /** @return template-type<T, Type, 'BSONType'> */
    public function getEncodedValue(object $object): mixed
    {
        $value = $this->getDecodedValue($object);

        return $this->type->encode($value);
    }

    /** @param template-type<T, Type, 'BSONType'> $value */
    public function setEncodedValue(object $object, mixed $value): void
    {
        $value = $this->type->decode($value);

        $this->setDecodedValue($object, $value);
    }
}
