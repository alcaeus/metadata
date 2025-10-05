<?php

declare(strict_types=1);

namespace Alcaeus\Metadata\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
final readonly class Id extends Field
{
    public function __construct()
    {
        parent::__construct('_id');
    }
}
