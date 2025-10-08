<?php

declare(strict_types=1);

namespace Alcaeus\Tests\Metadata\Type;

use Alcaeus\Metadata\Type\Hash;
use Alcaeus\Metadata\Type\Type;
use ArrayIterator;
use MongoDB\BSON\Document as BSONDocument;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

use function sprintf;

#[CoversClass(Hash::class)]
class HashTest extends TestCase
{
    public function testCanDecodeReturnsTrueForBSONDocument(): void
    {
        $type = new Hash();
        $document = BSONDocument::fromPHP(['foo' => 'bar']);

        self::assertTrue($type->canDecode($document));
    }

    public function testCanDecodeReturnsFalseForNonBSONDocument(): void
    {
        $type = new Hash();

        self::assertFalse($type->canDecode(['foo' => 'bar']));
        self::assertFalse($type->canDecode('string'));
        self::assertFalse($type->canDecode(123));
        self::assertFalse($type->canDecode(null));
    }

    public function testCanEncodeReturnsTrueForArray(): void
    {
        $type = new Hash();

        self::assertTrue($type->canEncode(['foo' => 'bar']));
        self::assertTrue($type->canEncode([]));
    }

    public function testCanEncodeReturnsFalseForNonArray(): void
    {
        $type = new Hash();

        self::assertFalse($type->canEncode('string'));
        self::assertFalse($type->canEncode(123));
        self::assertFalse($type->canEncode(null));
        self::assertFalse($type->canEncode(new ArrayIterator(['foo', 'bar'])));
    }

    public function testDecodeWithoutItemType(): void
    {
        $type = new Hash();
        $document = BSONDocument::fromPHP(['foo' => 'bar', 'bar' => 123]);

        $result = $type->decode($document);

        self::assertSame(['foo' => 'bar', 'bar' => 123], $result);
    }

    public function testDecodeWithItemType(): void
    {
        $itemType = $this->createMock(Type::class);
        $itemType->expects(self::exactly(3))
            ->method('decode')
            ->willReturnCallback(static fn ($value) => sprintf('decoded_%s', $value));

        $type = new Hash($itemType);
        $document = BSONDocument::fromPHP(['foo' => 'foo', 'bar' => 'bar', 'baz' => 'baz']);

        $result = $type->decode($document);

        self::assertSame(['foo' => 'decoded_foo', 'bar' => 'decoded_bar', 'baz' => 'decoded_baz'], $result);
    }

    public function testDecodeEmptyArray(): void
    {
        $type = new Hash();
        $document = BSONDocument::fromPHP([]);

        $result = $type->decode($document);

        self::assertSame([], $result);
    }

    public function testEncodeArrayWithoutItemType(): void
    {
        $type = new Hash();
        $input = ['foo' => 'foo', 'bar' => 'bar', 'baz' => 123];

        $result = $type->encode($input);

        self::assertEquals(
            BSONDocument::fromPHP(['foo' => 'foo', 'bar' => 'bar', 'baz' => 123]),
            $result,
        );
    }

    public function testEncodeArrayWithItemType(): void
    {
        $itemType = $this->createMock(Type::class);
        $itemType->expects(self::exactly(3)) // Now called only once per item
            ->method('encode')
            ->willReturnCallback(static fn ($value) => sprintf('encoded_%s', $value));

        $type = new Hash($itemType);
        $input = ['foo' => 'foo', 'bar' => 'bar', 'baz' => 'baz'];

        $result = $type->encode($input);

        self::assertEquals(
            BSONDocument::fromPHP(['foo' => 'encoded_foo', 'bar' => 'encoded_bar', 'baz' => 'encoded_baz']),
            $result,
        );
    }

    public function testEncodeEmptyArray(): void
    {
        $type = new Hash();
        $input = [];

        $result = $type->encode($input);

        self::assertEquals(BSONDocument::fromPHP([]), $result);
    }
}
