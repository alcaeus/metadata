<?php

declare(strict_types=1);

namespace Alcaeus\Tests\Metadata\Type;

use Alcaeus\Metadata\DocumentMetadataStore;
use Alcaeus\Metadata\Type\BoolType;
use Alcaeus\Metadata\Type\DateTime;
use Alcaeus\Metadata\Type\Document;
use Alcaeus\Metadata\Type\FloatType;
use Alcaeus\Metadata\Type\Hash;
use Alcaeus\Metadata\Type\IntType;
use Alcaeus\Metadata\Type\ObjectType;
use Alcaeus\Metadata\Type\PackedArray;
use Alcaeus\Metadata\Type\Raw;
use Alcaeus\Metadata\Type\StringType;
use Alcaeus\Metadata\Type\Type;
use Alcaeus\Metadata\Type\TypeGuesser;
use Alcaeus\Metadata\Type\Union;
use Alcaeus\Tests\Metadata\Attribute\Fixtures\TestDocument;
use Alcaeus\Tests\Metadata\Type\Fixtures\TestClass;
use DateTimeImmutable;
use Generator;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Stringable;
use Symfony\Component\TypeInfo\Type as TypeInfo;

#[CoversClass(TypeGuesser::class)]
class TypeGuesserTest extends TestCase
{
    #[DataProvider('provideTypesToGuess')]
    public function testGuessType(Type $expectedType, TypeInfo $type): void
    {
        self::assertEquals(
            $expectedType,
            TypeGuesser::guessType($type, new DocumentMetadataStore()),
        );
    }

    public static function provideTypesToGuess(): Generator
    {
        yield 'String' => [
            new StringType(),
            TypeInfo::string(),
        ];

        yield 'Int' => [
            new IntType(),
            TypeInfo::int(),
        ];

        yield 'Float' => [
            new FloatType(),
            TypeInfo::float(),
        ];

        yield 'Bool' => [
            new BoolType(),
            TypeInfo::bool(),
        ];

        yield 'Object without type' => [
            new ObjectType(),
            TypeInfo::object(),
        ];

        yield 'Object with class name' => [
            new ObjectType(TestClass::class),
            TypeInfo::object(TestClass::class),
        ];

        // Nullable types are not supported yet
//        yield 'Nullable string' => [
//            new Raw(),
//            TypeInfo::nullable(TypeInfo::string()),
//        ];

        yield 'List without value type specified' => [
            new PackedArray(new Raw()),
            TypeInfo::list(),
        ];

        yield 'Hash without value type specified' => [
            new Hash(new Raw()),
            TypeInfo::array(),
        ];

        yield 'List with value type specified' => [
            new PackedArray(new ObjectType(TestClass::class)),
            TypeInfo::list(TypeInfo::object(TestClass::class)),
        ];

        yield 'Hash with value type specified' => [
            new Hash(new ObjectType(TestClass::class)),
            TypeInfo::array(TypeInfo::object(TestClass::class)),
        ];

        yield 'Union type' => [
            new Union(
                new ObjectType(TestClass::class),
                new DateTime(),
            ),
            TypeInfo::union(TypeInfo::object(TestClass::class), TypeInfo::object(DateTimeImmutable::class)),
        ];

        $metadataStore = new DocumentMetadataStore();

        yield 'Mapped document' => [
            new Document($metadataStore->getMetadata(TestDocument::class)),
            TypeInfo::object(TestDocument::class),
        ];
    }

    public function testIntersectionTypesAreNotSupported(): void
    {
        $this->expectException(InvalidArgumentException::class);
        TypeGuesser::guessType(
            TypeInfo::intersection(
                TypeInfo::object(TestClass::class),
                TypeInfo::object(Stringable::class),
            ),
            new DocumentMetadataStore(),
        );
    }
}
