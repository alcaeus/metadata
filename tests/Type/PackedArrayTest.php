<?php

declare(strict_types=1);

namespace Alcaeus\Metadata\Tests\Type;

use Alcaeus\Metadata\Type\PackedArray;
use Alcaeus\Metadata\Type\Type;
use ArrayIterator;
use MongoDB\BSON\PackedArray as BSONPackedArray;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

use function sprintf;

#[CoversClass(PackedArray::class)]
class PackedArrayTest extends TestCase
{
    public function testCanDecodeReturnsTrueForBSONPackedArray(): void
    {
        $type = new PackedArray();
        $bsonArray = BSONPackedArray::fromPHP(['foo', 'bar']);

        self::assertTrue($type->canDecode($bsonArray));
    }

    public function testCanDecodeReturnsFalseForNonBSONPackedArray(): void
    {
        $type = new PackedArray();

        self::assertFalse($type->canDecode(['foo', 'bar']));
        self::assertFalse($type->canDecode('string'));
        self::assertFalse($type->canDecode(123));
        self::assertFalse($type->canDecode(null));
    }

    public function testCanEncodeReturnsTrueForArray(): void
    {
        $type = new PackedArray();

        self::assertTrue($type->canEncode(['foo', 'bar']));
        self::assertTrue($type->canEncode([]));
    }

    public function testCanEncodeReturnsFalseForNonArray(): void
    {
        $type = new PackedArray();

        self::assertFalse($type->canEncode('string'));
        self::assertFalse($type->canEncode(123));
        self::assertFalse($type->canEncode(null));
        self::assertFalse($type->canEncode(new ArrayIterator(['foo', 'bar'])));
    }

    public function testDecodeWithoutItemType(): void
    {
        $type = new PackedArray();
        $bsonArray = BSONPackedArray::fromPHP(['foo', 'bar', 123]);

        $result = $type->decode($bsonArray);

        self::assertSame(['foo', 'bar', 123], $result);
    }

    public function testDecodeWithItemType(): void
    {
        $itemType = $this->createMock(Type::class);
        $itemType->expects(self::exactly(3))
            ->method('decode')
            ->willReturnCallback(static fn ($value) => sprintf('decoded_%s', $value));

        $type = new PackedArray($itemType);
        $bsonArray = BSONPackedArray::fromPHP(['foo', 'bar', 'baz']);

        $result = $type->decode($bsonArray);

        self::assertSame(['decoded_foo', 'decoded_bar', 'decoded_baz'], $result);
    }

    public function testDecodeEmptyArray(): void
    {
        $type = new PackedArray();
        $bsonArray = BSONPackedArray::fromPHP([]);

        $result = $type->decode($bsonArray);

        self::assertSame([], $result);
    }

    public function testEncodeArrayWithoutItemType(): void
    {
        $type = new PackedArray();
        $input = ['foo', 'bar', 123];

        $result = $type->encode($input);

        self::assertEquals(
            BSONPackedArray::fromPHP(['foo', 'bar', 123]),
            $result,
        );
    }

    public function testEncodeArrayWithItemType(): void
    {
        $itemType = $this->createMock(Type::class);
        $itemType->expects(self::exactly(3)) // Now called only once per item
            ->method('encode')
            ->willReturnCallback(static fn ($value) => sprintf('encoded_%s', $value));

        $type = new PackedArray($itemType);
        $input = ['foo', 'bar', 'baz'];

        $result = $type->encode($input);

        self::assertEquals(
            BSONPackedArray::fromPHP(['encoded_foo', 'encoded_bar', 'encoded_baz']),
            $result,
        );
    }

    public function testEncodeEmptyArray(): void
    {
        $type = new PackedArray();
        $input = [];

        $result = $type->encode($input);

        self::assertEquals(BSONPackedArray::fromPHP([]), $result);
    }

    public function testEncodeAssociativeArrayConvertsToPackedArray(): void
    {
        $type = new PackedArray();
        $input = ['key1' => 'value1', 'key2' => 'value2'];

        $result = $type->encode($input);

        self::assertEquals(
            BSONPackedArray::fromPHP(['value1', 'value2']),
            $result,
        );
    }
}
