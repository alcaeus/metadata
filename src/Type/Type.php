<?php

declare(strict_types=1);

namespace Alcaeus\Metadata\Type;

use MongoDB\Codec\Codec;

/**
 * @template BSONType
 * @template NativeType
 * @template-extends Codec<BSONType, NativeType>
 */
interface Type extends Codec
{
}
