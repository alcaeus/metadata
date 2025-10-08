<?php

declare(strict_types=1);

namespace Alcaeus\Metadata\Exception\Loader;

use Alcaeus\Metadata\Exception\MetadataException;
use RuntimeException;

use function sprintf;

class UnmappedClass extends RuntimeException implements MetadataException
{
    public function __construct(string $className)
    {
        parent::__construct(sprintf('Class "%s" is not mapped as a document.', $className));
    }
}
