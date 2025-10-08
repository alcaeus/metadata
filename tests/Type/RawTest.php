<?php

declare(strict_types=1);

namespace Alcaeus\Tests\Metadata\Type;

use Alcaeus\Metadata\Type\Raw;
use Generator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(Raw::class)]
class RawTest extends TestCase
{
    public static function provideValues(): Generator
    {
        yield 'string' => ['test'];
        yield 'int' => [123];
        yield 'float' => [123.45];
        yield 'bool true' => [true];
        yield 'bool false' => [false];
        yield 'null' => [null];
        yield 'array' => [[1, 2, 3]];
        yield 'object' => [(object) ['a' => 1, 'b' => 2]];
    }

    #[DataProvider('provideValues')]
    public function testCanDecodeReturnsTrueForEverythingWeCanThinkOf(mixed $value): void
    {
        $type = new Raw();

        self::assertTrue($type->canDecode($value));
    }

    #[DataProvider('provideValues')]
    public function testCanEncodeReturnsTrueForEverythingWeCanThinkOf(mixed $value): void
    {
        $type = new Raw();

        self::assertTrue($type->canEncode($value));
    }

    #[DataProvider('provideValues')]
    public function testDecode(mixed $value): void
    {
        $type = new Raw();

        self::assertSame($value, $type->decode($value));
    }

    #[DataProvider('provideValues')]
    public function testEncode(mixed $value): void
    {
        $type = new Raw();

        self::assertSame($value, $type->encode($value));
    }
}
