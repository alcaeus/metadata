<?php

declare(strict_types=1);

namespace Alcaeus\Metadata\Type;

use Alcaeus\Metadata\DocumentMetadata;

interface HasDocumentMetadata
{
    // phpcs:disable
    public DocumentMetadata $documentMetadata {
        get;
    }
    // phpcs:enable
}
