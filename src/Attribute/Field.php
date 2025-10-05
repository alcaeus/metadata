<?php

declare(strict_types=1);

namespace Alcaeus\Metadata\Attribute;

use Alcaeus\Metadata\FieldMetadata;
use Attribute;
use ReflectionProperty;

#[Attribute(Attribute::TARGET_PROPERTY)]
readonly class Field
{
    public function __construct(
        public ?string $fieldName = null,
    ) {
    }

    public function createMetadata(ReflectionProperty $reflectionProperty): FieldMetadata
    {
        return new FieldMetadata(
            property: $reflectionProperty,
            fieldName: $this->fieldName ?? $reflectionProperty->name,
        );
    }
}
