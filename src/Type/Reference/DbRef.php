<?php

declare(strict_types=1);

namespace Alcaeus\Metadata\Type\Reference;

use MongoDB\BSON\Document;
use MongoDB\Exception\UnsupportedValueException;
use ReflectionClass;

final class DbRef extends Reference
{
    public function canDecode(mixed $value): bool
    {
        return $value instanceof Document
            && $value->has('$ref')
            && $value->has('$id')
            && $value->get('$ref') === $this->collectionMetadata->collectionName
            && $this->documentMetadata->identifier->type->canDecode($value->get('$id'));
    }

    public function decode(mixed $value): object
    {
        if (! $this->canDecode($value)) {
            throw UnsupportedValueException::invalidDecodableValue($value);
        }

        $object = $this->documentMetadata->class->newLazyGhost(
            static function (object $object): void {
                // TODO: How do we get a collection instance here?
            },
            ReflectionClass::SKIP_INITIALIZATION_ON_SERIALIZE,
        );

        $this->documentMetadata->identifier->setEncodedValue($object, $value->get('$id'));

        return $object;
    }

    public function encode(mixed $value): Document
    {
        if (! $this->canEncode($value)) {
            throw UnsupportedValueException::invalidEncodableValue($value);
        }

        return Document::fromPHP([
            '$ref' => $this->collectionMetadata->collectionName,
            '$id' => $this->getEncodedIdentifier($value),
        ]);
    }
}
