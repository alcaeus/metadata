<?php

declare(strict_types=1);

namespace Alcaeus\Metadata\Attribute\Reference;

use Alcaeus\Metadata\Attribute\Reference;
use Alcaeus\Metadata\CollectionMetadata;
use Alcaeus\Metadata\Type\Reference\DbRef;
use Alcaeus\Metadata\Type\Type;
use Attribute;

/** @template T of Type */
#[Attribute(Attribute::TARGET_PROPERTY)]
class AsDbRef extends Reference
{
    protected function createReferenceType(CollectionMetadata $collectionMetadata): DbRef
    {
        return new DbRef($collectionMetadata);
    }
}
