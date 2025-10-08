<?php

declare(strict_types=1);

namespace Alcaeus\Metadata\Type;

use MongoDB\Codec\DecodeIfSupported;
use MongoDB\Codec\EncodeIfSupported;

/** @template-implements Type<mixed, int> */
final readonly class IntType implements Type
{
    /** @use DecodeIfSupported<mixed, int> */
    use DecodeIfSupported;
    /** @use EncodeIfSupported<mixed, int> */
    use EncodeIfSupported;

    public function canDecode(mixed $value): bool
    {
        return true;
    }

    public function canEncode(mixed $value): bool
    {
        return true;
    }

    public function decode(mixed $value): int
    {
        return (int) $value;
    }

    public function encode(mixed $value): int
    {
        return (int) $value;
    }
}
