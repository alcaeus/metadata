<?php

declare(strict_types=1);

namespace Alcaeus\Metadata\Tests\Attribute\Fixtures;

use Alcaeus\Metadata\Attribute\Document;
use Alcaeus\Metadata\Attribute\Id;

#[Document]
class DocumentWithOnlyIdentifier
{
    #[Id]
    private string $id;

    private string $unmappedProperty;

    private static string $staticProperty;
}
