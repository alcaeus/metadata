<?php

declare(strict_types=1);

namespace Alcaeus\Tests\Metadata\Type;

use Alcaeus\Metadata\Type\StringType;
use Generator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(StringType::class)]
class StringTypeTest extends TestCase
{
    public static function provideValues(): Generator
    {
        yield 'string' => ['test', 'test'];
        yield 'int' => ['123', 123];
        yield 'float' => ['123.45', 123.45];
        yield 'bool true' => ['1', true];
        yield 'bool false' => ['', false];
        yield 'null' => ['', null];
        yield 'array' => ['Array', [1, 2, 3]];
    }

    #[DataProvider('provideValues')]
    public function testCanDecodeReturnsTrueForEverythingWeCanThinkOf(mixed $expected, mixed $value): void
    {
        $type = new StringType();

        self::assertTrue($type->canDecode($value));
    }

    #[DataProvider('provideValues')]
    public function testCanEncodeReturnsTrueForEverythingWeCanThinkOf(mixed $expected, mixed $value): void
    {
        $type = new StringType();

        self::assertTrue($type->canEncode($value));
    }

    #[DataProvider('provideValues')]
    public function testDecode(mixed $expected, mixed $value): void
    {
        $type = new StringType();

        self::assertSame($expected, $type->decode($value));
    }

    #[DataProvider('provideValues')]
    public function testEncode(mixed $expected, mixed $value): void
    {
        $type = new StringType();

        self::assertSame($expected, $type->encode($value));
    }
}
