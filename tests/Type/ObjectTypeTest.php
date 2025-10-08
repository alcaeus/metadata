<?php

declare(strict_types=1);

namespace Alcaeus\Tests\Metadata\Type;

use Alcaeus\Metadata\Type\ObjectType;
use Alcaeus\Tests\Metadata\Type\Fixtures\TestClass;
use ArrayIterator;
use MongoDB\BSON\Document as BSONDocument;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ObjectType::class)]
class ObjectTypeTest extends TestCase
{
    public function testCanDecodeReturnsTrueForBSONDocument(): void
    {
        $type = new ObjectType();
        $document = BSONDocument::fromPHP(['foo' => 'bar']);

        self::assertTrue($type->canDecode($document));
    }

    public function testCanDecodeReturnsFalseForNonBSONDocument(): void
    {
        $type = new ObjectType();

        self::assertFalse($type->canDecode(['foo' => 'bar']));
        self::assertFalse($type->canDecode('string'));
        self::assertFalse($type->canDecode(123));
        self::assertFalse($type->canDecode(null));
    }

    public function testCanEncodeReturnsTrueForStdClass(): void
    {
        $type = new ObjectType();

        self::assertTrue($type->canEncode((object) ['foo' => 'bar']));
        self::assertTrue($type->canEncode((object) []));
    }

    public function testCanEncodeReturnsTrueForOtherClass(): void
    {
        $type = new ObjectType(TestClass::class);

        $instance = new TestClass();

        self::assertTrue($type->canEncode($instance));
    }

    public function testCanEncodeReturnsFalseForNonStdClass(): void
    {
        $type = new ObjectType();

        self::assertFalse($type->canEncode(['foo' => 'bar']));
        self::assertFalse($type->canEncode([]));
        self::assertFalse($type->canEncode('string'));
        self::assertFalse($type->canEncode(123));
        self::assertFalse($type->canEncode(null));
        self::assertFalse($type->canEncode(new ArrayIterator(['foo', 'bar'])));
    }

    public function testCanEncodeReturnsFalseForNonOtherClass(): void
    {
        $type = new ObjectType(TestClass::class);

        self::assertFalse($type->canEncode((object) ['foo' => 'bar']));
    }

    public function testDecodeIntoStdClass(): void
    {
        $type = new ObjectType();
        $document = BSONDocument::fromPHP(['foo' => 'bar', 'bar' => 123]);

        $result = $type->decode($document);

        self::assertEquals((object) ['foo' => 'bar', 'bar' => 123], $result);
    }

    public function testDecodeIntoOtherClass(): void
    {
        $type = new ObjectType(TestClass::class);
        $document = BSONDocument::fromPHP(['foo' => 'bar', 'bar' => 123]);

        $result = $type->decode($document);

        self::assertEquals(
            new TestClass(foo: 'bar', bar: 123),
            $result,
        );
    }

    public function testDecodeEmptyObject(): void
    {
        $type = new ObjectType();
        $document = BSONDocument::fromPHP([]);

        $result = $type->decode($document);

        self::assertEquals((object) [], $result);
    }

    public function testEncodeStdClass(): void
    {
        $type = new ObjectType();
        $input = (object) ['foo' => 'foo', 'bar' => 'bar', 'baz' => 123];

        $result = $type->encode($input);

        self::assertEquals(
            BSONDocument::fromPHP(['foo' => 'foo', 'bar' => 'bar', 'baz' => 123]),
            $result,
        );
    }

    public function testEncodeOtherClass(): void
    {
        $type = new ObjectType(TestClass::class);
        $input = new TestClass(foo: 'foo', bar: 'bar', baz: 123);

        $result = $type->encode($input);

        self::assertEquals(
            BSONDocument::fromPHP(['foo' => 'foo', 'bar' => 'bar', 'baz' => 123]),
            $result,
        );
    }

    public function testEncodeEmptyObject(): void
    {
        $type = new ObjectType();
        $input = (object) [];

        $result = $type->encode($input);

        self::assertEquals(BSONDocument::fromPHP([]), $result);
    }
}
