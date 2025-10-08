<?php

declare(strict_types=1);

namespace Alcaeus\Metadata\Type;

use MongoDB\Codec\DecodeIfSupported;
use MongoDB\Codec\EncodeIfSupported;

/** @template-implements Type<mixed, mixed> */
final readonly class Raw implements Type
{
    /** @use DecodeIfSupported<mixed, mixed> */
    use DecodeIfSupported;
    /** @use EncodeIfSupported<mixed, mixed> */
    use EncodeIfSupported;

    public function canDecode(mixed $value): bool
    {
        return true;
    }

    public function canEncode(mixed $value): bool
    {
        return true;
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
        return $value;
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
        return $value;
    }
}
