<?php

declare(strict_types=1);

namespace Alcaeus\Metadata\Attribute;

use Alcaeus\Metadata\Type\Type;
use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
final class Id extends Field
{
    public function __construct(?Type $type = null)
    {
        parent::__construct(
            fieldName: '_id',
            type: $type,
        );
    }
}
