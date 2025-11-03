<?php

declare(strict_types=1);

namespace Alcaeus\Metadata;

use Alcaeus\Graph\Graph;
use Alcaeus\Metadata\Exception\Loader\UnmappedClass;
use Alcaeus\Metadata\Loader\AttributeLoader;
use Alcaeus\Metadata\Loader\Loader;
use Alcaeus\Metadata\Type\HasDocumentMetadata;
use Alcaeus\Metadata\Type\Type;

final readonly class DocumentMetadataStore
{
    /** @var Graph<DocumentMetadata<object>, FieldMetadata<Type<mixed, mixed>>> $graph */
    private Graph $graph;

    public function __construct(private Loader $loader = new AttributeLoader())
    {
        $this->graph = new Graph();
    }

    /**
     * @param class-string<T> $className
     *
     * @return DocumentMetadata<T>
     *
     * @template T of object
     */
    public function getMetadata(string $className): DocumentMetadata
    {
        if ($this->graph->hasNode($className)) {
            return $this->graph->getNode($className)->data;
        }

        return $this->loadMetadata($className);
    }

    /**
     * @param class-string<T> $className
     *
     * @return DocumentMetadata<T>|null
     *
     * @template T of object
     */
    public function tryGetMetadata(string $className): ?DocumentMetadata
    {
        if ($this->graph->hasNode($className)) {
            return $this->graph->getNode($className)->data;
        }

        try {
            return $this->loadMetadata($className);
        } catch (UnmappedClass) {
            return null;
        }
    }

    public function hasMetadata(string $className): bool
    {
        return $this->graph->hasNode($className);
    }

    /**
     * @param class-string<T> $className
     *
     * @return DocumentMetadata<T>
     *
     * @template T of object
     */
    public function loadMetadata(string $className): DocumentMetadata
    {
        $metadata = $this->loader->load($className, $this);

        $this->graph->addNode($className, $metadata);

        foreach ($metadata->fields as $field) {
            if (! $field->type instanceof HasDocumentMetadata) {
                continue;
            }

            $this->graph->connect($className, $field->type->metadata->className, $field);
        }

        return $metadata;
    }
}
