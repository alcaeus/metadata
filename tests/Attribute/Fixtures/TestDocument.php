<?php

declare(strict_types=1);

namespace Alcaeus\Tests\Metadata\Attribute\Fixtures;

use Alcaeus\Metadata\Attribute\Document;
use Alcaeus\Metadata\Attribute\Field;
use Alcaeus\Metadata\Attribute\Id;
use Alcaeus\Metadata\Type\DateTime;
use DateTime as NativeDateTime;
use DateTimeImmutable;
use DateTimeInterface;

#[Document]
class TestDocument
{
    #[Id]
    private string $id;

    #[Field]
    private string $field;

    #[Field(fieldName: 'custom_name')]
    private string $renamedField;

    #[Field(type: new DateTime())]
    private DateTimeInterface $typedDateTimeImmutableField;

    #[Field]
    private NativeDateTime $guessedDateTimeField;

    #[Field]
    private ?NativeDateTime $nullableDateTimeField = null;

    #[Field]
    private NativeDateTime|DateTimeImmutable $unionTypedField;

    #[Field]
    // @phpcs:ignore SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingTraversableTypeHintSpecification
    private array $arrayFieldWithoutPHPDoc;

    /** @var list<string> */
    #[Field]
    private array $arrayFieldWithPHPDocAsListOfStrings;

    #[Field]
    private DocumentWithOnlyIdentifier $embeddedDocument;

    /** @var list<DocumentWithOnlyIdentifier> */
    #[Field]
    private array $embeddedDocumentsWithPHPDoc;

    /*
     * TODO: This no longer works as we're autodetecting types. PHPDoc only for now
    #[Embedded(class: DocumentWithOnlyIdentifier::class)]
    private array $embeddedDocumentsWithoutPHPDoc;
    */
}
