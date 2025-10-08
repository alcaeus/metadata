<?php

declare(strict_types=1);

namespace Alcaeus\Metadata\Type;

use MongoDB\Codec\DecodeIfSupported;
use MongoDB\Codec\EncodeIfSupported;

/** @template-implements Type<mixed, bool> */
final readonly class BoolType implements Type
{
    /** @use DecodeIfSupported<mixed, bool> */
    use DecodeIfSupported;
    /** @use EncodeIfSupported<mixed, bool> */
    use EncodeIfSupported;

    public function canDecode(mixed $value): bool
    {
        return true;
    }

    public function canEncode(mixed $value): bool
    {
        return true;
    }

    public function decode(mixed $value): bool
    {
        return (bool) $value;
    }

    public function encode(mixed $value): bool
    {
        return (bool) $value;
    }
}
