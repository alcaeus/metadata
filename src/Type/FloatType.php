<?php

declare(strict_types=1);

namespace Alcaeus\Metadata\Type;

use MongoDB\Codec\DecodeIfSupported;
use MongoDB\Codec\EncodeIfSupported;

/** @template-implements Type<mixed, float> */
final readonly class FloatType implements Type
{
    /** @use DecodeIfSupported<mixed, float> */
    use DecodeIfSupported;
    /** @use EncodeIfSupported<mixed, float> */
    use EncodeIfSupported;

    public function canDecode(mixed $value): bool
    {
        return true;
    }

    public function canEncode(mixed $value): bool
    {
        return true;
    }

    public function decode(mixed $value): float
    {
        return (float) $value;
    }

    public function encode(mixed $value): float
    {
        return (float) $value;
    }
}
