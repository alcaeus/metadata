<?php

declare(strict_types=1);

namespace Alcaeus\Metadata\Tests\Fixtures;

use Alcaeus\Metadata\Attribute\Document;
use Alcaeus\Metadata\Attribute\Field;
use Alcaeus\Metadata\Attribute\Id;

#[Document]
class TestDocumentA
{
    #[Field]
    public string $notes;

    public function __construct(
        #[Id]
        public readonly int $id,
        #[Field(fieldName: 'fullName')]
        public readonly string $name,
    ) {
    }
}
