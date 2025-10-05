<?php

declare(strict_types=1);

namespace Alcaeus\Metadata;

use Alcaeus\Metadata\Type\Type;
use ReflectionProperty;

/**
 * @template BSONType
 * @template NativeType
 */
class FieldMetadata implements Metadata
{
    // phpcs:disable
    public string $propertyName {
        get => $this->property->name;
    }
    // phpcs:enable

    /** @param Type<BSONType, NativeType>|null $type */
    public function __construct(
        public readonly ReflectionProperty $property,
        public readonly string $fieldName,
        public readonly ?Type $type = null,
    ) {
    }

    /** @return NativeType */
    public function getDecodedValue(object $object): mixed
    {
        return $this->property->getRawValue($object);
    }

    /** @param NativeType $value */
    public function setDecodedValue(object $object, mixed $value): void
    {
        $this->property->setRawValueWithoutLazyInitialization($object, $value);
    }

    /** @return BSONType */
    public function getEncodedValue(object $object): mixed
    {
        $value = $this->getDecodedValue($object);

        return $this->type?->encode($value) ?? $value;
    }

    /** @param BSONType $value */
    public function setEncodedValue(object $object, mixed $value): void
    {
        $value = $this->type?->decode($value) ?? $value;

        $this->setDecodedValue($object, $value);
    }
}
