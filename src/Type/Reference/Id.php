<?php

declare(strict_types=1);

namespace Alcaeus\Metadata\Type\Reference;

use MongoDB\Exception\UnsupportedValueException;
use ReflectionClass;

final class Id extends Reference
{
    public function canDecode(mixed $value): bool
    {
        return $this->documentMetadata->identifier->type->canDecode($value);
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

        $this->documentMetadata->identifier->setEncodedValue($object, $value);

        return $object;
    }

    public function encode(mixed $value): mixed
    {
        if (! $this->canEncode($value)) {
            throw UnsupportedValueException::invalidEncodableValue($value);
        }

        return $this->getEncodedIdentifier($value);
    }
}
