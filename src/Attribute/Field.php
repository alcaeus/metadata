<?php

declare(strict_types=1);

namespace Alcaeus\Metadata\Attribute;

use Alcaeus\Metadata\FieldMetadata;
use Alcaeus\Metadata\Type\DateTime;
use Alcaeus\Metadata\Type\PackedArray;
use Alcaeus\Metadata\Type\Raw;
use Alcaeus\Metadata\Type\Type;
use Attribute;
use DateTime as NativeDateTime;
use DateTimeImmutable;
use ReflectionNamedType;
use ReflectionProperty;
use ReflectionType;

use function is_a;

/**
 * @template T of Type
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
readonly class Field
{
    /** @param T|null $type */
    public function __construct(
        public ?string $fieldName = null,
        public ?Type $type = null,
    ) {
    }

    /** @return FieldMetadata<T> */
    public function createMetadata(ReflectionProperty $reflectionProperty): FieldMetadata
    {
        return new FieldMetadata(
            property: $reflectionProperty,
            fieldName: $this->fieldName ?? $reflectionProperty->name,
            type: $this->type ?? $this->guessType($reflectionProperty),
        );
    }

    /** @return T */
    protected function guessType(ReflectionProperty $reflectionProperty): Type
    {
        $propertyType = $reflectionProperty->getType();

        if ($this->isDateTimeType($propertyType)) {
            return new DateTime($propertyType->getName());
        }

        if ($this->isArray($propertyType)) {
            // TODO: Guess nested type
            return new PackedArray();
        }

        return new Raw();
    }

    /** @phpstan-assert-if-true ReflectionNamedType $type */
    protected function isDateTimeType(?ReflectionType $type): bool
    {
        if (! $type instanceof ReflectionNamedType) {
            return false;
        }

        return is_a($type->getName(), NativeDateTime::class, true)
            || is_a($type->getName(), DateTimeImmutable::class, true);
    }

    protected function isArray(ReflectionType $type): bool
    {
        return $type instanceof ReflectionNamedType && $type->getName() === 'array';
    }
}
