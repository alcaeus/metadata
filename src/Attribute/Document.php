<?php

declare(strict_types=1);

namespace Alcaeus\Metadata\Attribute;

use Alcaeus\Metadata\DocumentMetadata;
use Attribute;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionProperty;
use RuntimeException;

use function sprintf;

#[Attribute(Attribute::TARGET_CLASS)]
class Document
{
    /**
     * @param ReflectionClass<T> $reflectionClass
     *
     * @return DocumentMetadata<T>
     *
     * @template T of object
     */
    public function createMetadata(ReflectionClass $reflectionClass): DocumentMetadata
    {
        $identifier = null;
        $fields = [];

        foreach ($reflectionClass->getProperties() as $reflectionProperty) {
            if ($this->isExcludedProperty($reflectionProperty)) {
                continue;
            }

            $attributes = $reflectionProperty->getAttributes(
                Field::class,
                ReflectionAttribute::IS_INSTANCEOF,
            );
            if (! $attributes) {
                continue;
            }

            $fieldAttribute = $attributes[0]->newInstance();
            $fieldMetadata = $fieldAttribute->createMetadata($reflectionProperty);

            $fields[$reflectionProperty->name] = $fieldMetadata;

            if (! ($fieldAttribute instanceof Id)) {
                continue;
            }

            $identifier = $fieldMetadata;
        }

        if (! $identifier) {
            throw new RuntimeException(
                sprintf('No identifier defined for document class "%s"', $reflectionClass->name),
            );
        }

        return new DocumentMetadata($reflectionClass, $identifier, $fields);
    }

    public function isExcludedProperty(ReflectionProperty $property): bool
    {
        return $property->isStatic();
    }
}
