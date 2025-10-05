<?php

declare(strict_types=1);

namespace Alcaeus\Metadata;

use ReflectionClass;

/** @template T of object */
final class DocumentMetadata implements Metadata
{
    // phpcs:disable
    /** @var class-string<T> */
    public string $className {
        get => $this->class->name;
    }
    // phpcs:enable

    /**
     * @param ReflectionClass<T> $class
     * @param FieldMetadata<mixed, mixed> $identifier
     * @param array<string, FieldMetadata<mixed, mixed>> ...$fields
     */
    public function __construct(
        public readonly ReflectionClass $class,
        public readonly FieldMetadata $identifier,
        public readonly array $fields,
    ) {
    }
}
