<?php

declare(strict_types=1);

namespace Alcaeus\Metadata\Type;

interface WrappingType
{
    // phpcs:disable
    public ?Type $wrappedType {
        get;
    }
    // phpcs:enable
}
