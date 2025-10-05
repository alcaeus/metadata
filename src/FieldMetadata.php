<?php

declare(strict_types=1);

namespace Alcaeus\Metadata;

use ReflectionProperty;

class FieldMetadata implements Metadata
{
    // phpcs:disable
    public string $propertyName {
        get => $this->property->name;
    }
    // phpcs:enable

    public function __construct(
        public readonly ReflectionProperty $property,
        public readonly string $fieldName,
    ) {
    }
}
