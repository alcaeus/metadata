<?php

declare(strict_types=1);

namespace Alcaeus\Metadata;

/** @template T of object */
final readonly class CollectionMetadata implements Metadata
{
    /** @param DocumentMetadata<T> $documentMetadata */
    public function __construct(
        public string $collectionName,
        public DocumentMetadata $documentMetadata,
    ) {
    }
}
