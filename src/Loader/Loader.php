<?php

declare(strict_types=1);

namespace Alcaeus\Metadata\Loader;

use Alcaeus\Metadata\DocumentMetadata;

interface Loader
{
    /**
     * @param class-string<T> $className
     *
     * @return DocumentMetadata<T>
     *
     * @template T of object
     */
    public function load(string $className): DocumentMetadata;
}
