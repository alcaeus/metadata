<?php

declare(strict_types=1);

namespace Alcaeus\Metadata\Attribute;

use Alcaeus\Metadata\DocumentMetadataStore;
use Alcaeus\Metadata\FieldMetadata;
use Alcaeus\Metadata\Type\Type;
use Alcaeus\Metadata\Type\TypeGuesser;
use Attribute;
use ReflectionProperty;
use Symfony\Component\TypeInfo\TypeResolver\TypeResolver;

/** @template T of Type */
#[Attribute(Attribute::TARGET_PROPERTY)]
class Field
{
    private static TypeResolver $typeResolver;

    /** @param T|null $type */
    public function __construct(
        public readonly ?string $fieldName = null,
        public readonly ?Type $type = null,
    ) {
    }

    /** @return FieldMetadata<T> */
    public function createMetadata(ReflectionProperty $reflectionProperty, DocumentMetadataStore $store): FieldMetadata
    {
        return new FieldMetadata(
            property: $reflectionProperty,
            fieldName: $this->fieldName ?? $reflectionProperty->name,
            type: $this->type ?? $this->guessType($reflectionProperty, $store),
        );
    }

    /** @return T */
    protected function guessType(ReflectionProperty $reflectionProperty, DocumentMetadataStore $store): Type
    {
        return TypeGuesser::guessType(
            $this->getTypeResolver()->resolve($reflectionProperty),
            $store,
        );
    }

    protected function getTypeResolver(): TypeResolver
    {
        return static::$typeResolver ??= TypeResolver::create();
    }
}
