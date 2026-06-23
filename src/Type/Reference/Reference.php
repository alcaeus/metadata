<?php

declare(strict_types=1);

namespace Alcaeus\Metadata\Type\Reference;

use Alcaeus\Metadata\CollectionMetadata;
use Alcaeus\Metadata\DocumentMetadata;
use Alcaeus\Metadata\Type\HasDocumentMetadata;
use Alcaeus\Metadata\Type\Type;
use MongoDB\Codec\DecodeIfSupported;
use MongoDB\Codec\EncodeIfSupported;

/**
 * @template DocumentType of object
 * @template-implements Type<mixed, DocumentType>
 */
abstract class Reference implements Type, HasDocumentMetadata
{
    /** @use DecodeIfSupported<mixed, DocumentType> */
    use DecodeIfSupported;
    /** @use EncodeIfSupported<mixed, DocumentType> */
    use EncodeIfSupported;

    // phpcs:disable
    public DocumentMetadata $documentMetadata {
        get => $this->collectionMetadata->documentMetadata;
    }
    // phpcs:enable

    public function __construct(
        public readonly CollectionMetadata $collectionMetadata,
    ) {
    }

    public function canEncode(mixed $value): bool
    {
        return $value instanceof $this->documentMetadata->className;
    }

    protected function getEncodedIdentifier(mixed $value): mixed
    {
        return $this->documentMetadata->identifier->getEncodedValue($value);
    }
}
