<?php

declare(strict_types=1);

namespace Alcaeus\Tests\Metadata\Type;

use Alcaeus\Metadata\Type\FloatType;
use Generator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(FloatType::class)]
class FloatTypeTest extends TestCase
{
    public static function provideValues(): Generator
    {
        yield 'string' => [0.0, 'test'];
        yield 'int' => [123.0, 123];
        yield 'float' => [123.45, 123.45];
        yield 'bool true' => [1.0, true];
        yield 'bool false' => [0.0, false];
        yield 'null' => [0.0, null];
        yield 'array' => [1.0, [1, 2, 3]];
    }

    #[DataProvider('provideValues')]
    public function testCanDecodeReturnsTrueForEverythingWeCanThinkOf(mixed $expected, mixed $value): void
    {
        $type = new FloatType();

        self::assertTrue($type->canDecode($value));
    }

    #[DataProvider('provideValues')]
    public function testCanEncodeReturnsTrueForEverythingWeCanThinkOf(mixed $expected, mixed $value): void
    {
        $type = new FloatType();

        self::assertTrue($type->canEncode($value));
    }

    #[DataProvider('provideValues')]
    public function testDecode(mixed $expected, mixed $value): void
    {
        $type = new FloatType();

        self::assertSame($expected, $type->decode($value));
    }

    #[DataProvider('provideValues')]
    public function testEncode(mixed $expected, mixed $value): void
    {
        $type = new FloatType();

        self::assertSame($expected, $type->encode($value));
    }
}
