<?php

declare(strict_types=1);

namespace Alcaeus\Metadata\Type;

use MongoDB\Codec\DecodeIfSupported;
use MongoDB\Codec\EncodeIfSupported;
use MongoDB\Exception\UnsupportedValueException;

use function array_any;
use function array_values;

final readonly class Union implements Type
{
    /** @use DecodeIfSupported<mixed, mixed> */
    use DecodeIfSupported;
    /** @use EncodeIfSupported<mixed, mixed> */
    use EncodeIfSupported;

    /** @var list<Type> */
    public array $types;

    public function __construct(Type ...$types)
    {
        $this->types = array_values($types);
    }

    public function canDecode(mixed $value): bool
    {
        return array_any($this->types, static fn (Type $type) => $type->canDecode($value));
    }

    public function canEncode(mixed $value): bool
    {
        return array_any($this->types, static fn (Type $type) => $type->canEncode($value));
    }

    /**
     * @param T $value
     *
     * @return T
     *
     * @template T
     */
    public function decode(mixed $value): mixed
    {
        foreach ($this->types as $type) {
            if ($type->canDecode($value)) {
                return $type->decode($value);
            }
        }

        throw UnsupportedValueException::invalidDecodableValue($value);
    }

    /**
     * @param T $value
     *
     * @return T
     *
     * @template T
     */
    public function encode(mixed $value): mixed
    {
        foreach ($this->types as $type) {
            if ($type->canEncode($value)) {
                return $type->encode($value);
            }
        }

        throw UnsupportedValueException::invalidEncodableValue($value);
    }
}
