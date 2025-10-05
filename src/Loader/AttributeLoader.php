<?php

declare(strict_types=1);

namespace Alcaeus\Metadata\Loader;

use Alcaeus\Metadata\Attribute\Document;
use Alcaeus\Metadata\DocumentMetadata;
use InvalidArgumentException;
use ReflectionAttribute;
use ReflectionClass;

use function sprintf;

final class AttributeLoader implements Loader
{
    /**
     * @param class-string<T> $className
     *
     * @return DocumentMetadata<T>
     *
     * @template T of object
     */
    public function load(string $className): DocumentMetadata
    {
        $reflectionClass = new ReflectionClass($className);
        $attributes = $reflectionClass->getAttributes(Document::class, ReflectionAttribute::IS_INSTANCEOF);
        if (! $attributes) {
            throw new InvalidArgumentException(sprintf('Class "%s" is not mapped as a document', $className));
        }

        $documentAttribute = $attributes[0]->newInstance();

        return $documentAttribute->createMetadata($reflectionClass);
    }
}
