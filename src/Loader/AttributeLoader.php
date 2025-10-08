<?php

declare(strict_types=1);

namespace Alcaeus\Metadata\Loader;

use Alcaeus\Metadata\Attribute\Document;
use Alcaeus\Metadata\DocumentMetadata;
use Alcaeus\Metadata\DocumentMetadataStore;
use Alcaeus\Metadata\Exception\Loader\UnmappedClass;
use ReflectionAttribute;
use ReflectionClass;

use function assert;

final class AttributeLoader implements Loader
{
    /**
     * @param class-string<T> $className
     *
     * @return DocumentMetadata<T>
     *
     * @template T of object
     */
    public function load(string $className, DocumentMetadataStore $store): DocumentMetadata
    {
        $reflectionClass = new ReflectionClass($className);
        $attributes = $reflectionClass->getAttributes(Document::class, ReflectionAttribute::IS_INSTANCEOF);
        if (! $attributes) {
            throw new UnmappedClass($className);
        }

        $documentAttribute = $attributes[0]->newInstance();
        assert($documentAttribute instanceof Document);

        return $documentAttribute->createMetadata($reflectionClass, $store);
    }
}
