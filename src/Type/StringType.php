<?php

declare(strict_types=1);

namespace Alcaeus\Metadata\Type;

use MongoDB\Codec\DecodeIfSupported;
use MongoDB\Codec\EncodeIfSupported;

/** @template-implements Type<mixed, string> */
final readonly class StringType implements Type
{
    /** @use DecodeIfSupported<mixed, string> */
    use DecodeIfSupported;
    /** @use EncodeIfSupported<mixed, string> */
    use EncodeIfSupported;

    public function canDecode(mixed $value): bool
    {
        return true;
    }

    public function canEncode(mixed $value): bool
    {
        return true;
    }

    public function decode(mixed $value): string
    {
        return (string) $value;
    }

    public function encode(mixed $value): string
    {
        return (string) $value;
    }
}
