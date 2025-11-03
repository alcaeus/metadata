<?php

declare(strict_types=1);

namespace Alcaeus\Metadata\Type;

use Alcaeus\Metadata\DocumentMetadata;
use MongoDB\BSON\Document as BSONDocument;
use MongoDB\Codec\DecodeIfSupported;
use MongoDB\Codec\DocumentCodec;
use MongoDB\Codec\EncodeIfSupported;
use ReflectionClass;

/**
 * @template DocumentType of object
 * @template-implements DocumentCodec<DocumentType>
 * @template-implements Type<BSONDocument, DocumentType>
 */
final readonly class Document implements Type, DocumentCodec, HasDocumentMetadata
{
    /** @use DecodeIfSupported<BSONDocument, DocumentType> */
    use DecodeIfSupported;
    /** @use EncodeIfSupported<BSONDocument, DocumentType> */
    use EncodeIfSupported;

    /** @param DocumentMetadata<DocumentType> $documentMetadata */
    public function __construct(public private(set) DocumentMetadata $documentMetadata)
    {
    }

    public function canDecode(mixed $value): bool
    {
        return $value instanceof BSONDocument;
    }

    public function canEncode(mixed $value): bool
    {
        return $value instanceof $this->documentMetadata->className;
    }

    /**
     * @param BSONDocument $value
     *
     * @return DocumentType
     */
    public function decode(mixed $value): object
    {
        $object = $this->documentMetadata->class->newLazyGhost(
            function (object $object) use ($value): void {
                /**
                 * TODO: iterating over the metadata fields leads to loads of random accesses in the BSON document.
                 * This could be optimised by iterating over the BSON document instead and looking up the field metadata
                 * by field name. For this, DocumentMetadata should keep two arrays of fields, one indexed by field name
                 * and one by property name.
                 * Note that iterating over the BSON document leads to copying loads of data. This could be avoided by
                 * having a more efficient BSON document in future that allows indexing the entire document before
                 * working with it, returning just keys, etc.
                 */
                foreach ($this->documentMetadata->fields as $field) {
                    // The identifier is already initialised, no need to do it again
                    if ($field === $this->documentMetadata->identifier) {
                        continue;
                    }

                    if (! $value->has($field->fieldName)) {
                        continue;
                    }

                    $field->setEncodedValue($object, $value->get($field->fieldName));
                }
            },
            ReflectionClass::SKIP_INITIALIZATION_ON_SERIALIZE,
        );

        $this->documentMetadata->identifier->setEncodedValue($object, $value->get($this->documentMetadata->identifier->fieldName));

        return $object;
    }

    /** @param DocumentType $value */
    public function encode(mixed $value): BSONDocument
    {
        $fields = [];
        foreach ($this->documentMetadata->fields as $field) {
            $fields[$field->fieldName] = $field->getEncodedValue($value);
        }

        return BSONDocument::fromPHP($fields);
    }
}
