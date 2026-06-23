<?php

declare(strict_types=1);

namespace Alcaeus\Metadata\Attribute;

use Alcaeus\Metadata\CollectionMetadata;
use Alcaeus\Metadata\DocumentMetadataStore;
use Alcaeus\Metadata\FieldMetadata;
use Alcaeus\Metadata\Type\Document as DocumentType;
use Alcaeus\Metadata\Type\Reference\Reference as ReferenceType;
use Alcaeus\Metadata\Type\Type;
use Alcaeus\Metadata\Type\WrappingType;
use Attribute;
use InvalidArgumentException;
use ReflectionProperty;
use Symfony\Component\TypeInfo\TypeResolver\TypeResolver;

use function sprintf;

/** @template T of Type */
#[Attribute(Attribute::TARGET_PROPERTY)]
abstract class Reference extends Field
{
    private static TypeResolver $typeResolver;

    /** @param T|null $type */
    public function __construct(
        public readonly string $collectionName,
        public readonly ?string $fieldName = null,
        public readonly ?string $targetClass = null,
    ) {
    }

    abstract protected function createReferenceType(CollectionMetadata $collectionMetadata): ReferenceType;

    /** @return FieldMetadata<T> */
    public function createMetadata(ReflectionProperty $reflectionProperty, DocumentMetadataStore $store): FieldMetadata
    {
        if ($this->targetClass) {
            $type = new DocumentType($store->getMetadata($this->targetClass));
        } else {
            $type = $this->guessType($reflectionProperty, $store);
            if (! $type instanceof DocumentType) {
                throw new InvalidArgumentException(sprintf(
                    'Could not detect target class for reference "%s" in class "%s". Please set the "targetClass" parameter in the attribute.',
                    $reflectionProperty->name,
                    $reflectionProperty->class,
                ));
            }
        }

        return new FieldMetadata(
            property: $reflectionProperty,
            fieldName: $this->fieldName ?? $reflectionProperty->name,
            type: ,
        );
    }

    private function detectReferenceType(ReflectionProperty $reflectionProperty, DocumentMetadataStore $store): ReferenceType
    {
        if ($this->targetClass) {
            return $this->createReferenceType(
                new CollectionMetadata(
                    $this->collectionName,
                    $store->getMetadata($this->targetClass),
                ),
            );
        }

        $type = $this->guessType($reflectionProperty, $store);

        if ($type instanceof WrappingType) {
            $type = $type->wrappedType;
        }

        if (! $type instanceof DocumentType) {
            throw new InvalidArgumentException(sprintf(
                'Could not detect target class for reference "%s" in class "%s". Please set the "targetClass" parameter in the attribute.',
                $reflectionProperty->name,
                $reflectionProperty->class,
            ));
        }

        return $type;
    }
}
