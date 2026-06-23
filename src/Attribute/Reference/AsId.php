<?php

declare(strict_types=1);

namespace Alcaeus\Metadata\Attribute\Reference;

use Alcaeus\Metadata\CollectionMetadata;
use Alcaeus\Metadata\Attribute\Reference;
use Alcaeus\Metadata\Type\Reference\Id as IdType;
use Alcaeus\Metadata\Type\Type;
use Attribute;

/** @template T of Type */
#[Attribute(Attribute::TARGET_PROPERTY)]
class AsId extends Reference
{
    protected function createReferenceType(CollectionMetadata $collectionMetadata): IdType
    {
        return new IdType($collectionMetadata);
    }
}
