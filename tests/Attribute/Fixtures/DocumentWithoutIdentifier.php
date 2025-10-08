<?php

declare(strict_types=1);

namespace Alcaeus\Tests\Metadata\Attribute\Fixtures;

use Alcaeus\Metadata\Attribute\Document;
use Alcaeus\Metadata\Attribute\Field;

#[Document]
class DocumentWithoutIdentifier
{
    #[Field]
    private string $field;
}
