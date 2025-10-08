<?php

declare(strict_types=1);

namespace Alcaeus\Tests\Metadata\Type;

use Alcaeus\Metadata\Type\BoolType;
use Generator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(BoolType::class)]
class BoolTypeTest extends TestCase
{
    public static function provideValues(): Generator
    {
        yield 'string' => [true, 'test'];
        yield 'int' => [true, 123];
        yield 'float' => [true, 123.45];
        yield 'bool true' => [true, true];
        yield 'bool false' => [false, false];
        yield 'null' => [false, null];
        yield 'array' => [true, [1, 2, 3]];
    }

    #[DataProvider('provideValues')]
    public function testCanDecodeReturnsTrueForEverythingWeCanThinkOf(mixed $expected, mixed $value): void
    {
        $type = new BoolType();

        self::assertTrue($type->canDecode($value));
    }

    #[DataProvider('provideValues')]
    public function testCanEncodeReturnsTrueForEverythingWeCanThinkOf(mixed $expected, mixed $value): void
    {
        $type = new BoolType();

        self::assertTrue($type->canEncode($value));
    }

    #[DataProvider('provideValues')]
    public function testDecode(mixed $expected, mixed $value): void
    {
        $type = new BoolType();

        self::assertSame($expected, $type->decode($value));
    }

    #[DataProvider('provideValues')]
    public function testEncode(mixed $expected, mixed $value): void
    {
        $type = new BoolType();

        self::assertSame($expected, $type->encode($value));
    }
}
