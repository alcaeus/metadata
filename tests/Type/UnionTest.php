<?php

declare(strict_types=1);

namespace Alcaeus\Tests\Metadata\Type;

use Alcaeus\Metadata\Type\DateTime;
use Alcaeus\Metadata\Type\ObjectType;
use Alcaeus\Metadata\Type\Union;
use DateTimeImmutable;
use MongoDB\BSON\Document as BSONDocument;
use MongoDB\BSON\UTCDateTime;
use MongoDB\Exception\UnsupportedValueException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Union::class)]
class UnionTest extends TestCase
{
    public function testCanDecodeReturnsTrueIfOneTypeCanDecode(): void
    {
        $type = $this->createUnionType();

        $bsonDocument = BSONDocument::fromPHP(['foo' => 'bar']);

        self::assertTrue($type->canDecode($bsonDocument));
        self::assertTrue($type->canDecode(new UTCDateTime()));
    }

    public function testCanDecodeReturnsFalseIfNoTypeCanDecode(): void
    {
        $type = $this->createUnionType();

        self::assertFalse($type->canDecode((object) ['foo' => 'bar']));
        self::assertFalse($type->canDecode('just a string'));
    }

    public function testDecodeWithBSONDocument(): void
    {
        $type = $this->createUnionType();

        $result = $type->decode(BSONDocument::fromPHP(['foo' => 'bar']));

        self::assertEquals((object) ['foo' => 'bar'], $result);
    }

    public function testDecodeWithUTCDateTime(): void
    {
        $type = $this->createUnionType();

        $bsonDateTime = new UTCDateTime();
        $result = $type->decode($bsonDateTime);

        self::assertInstanceOf(DateTimeImmutable::class, $result);
        self::assertEquals($bsonDateTime->toDateTime(), $result);
    }

    public function testDecodeWithUnsupportedValue(): void
    {
        $type = $this->createUnionType();

        $this->expectExceptionObject(UnsupportedValueException::invalidDecodableValue('just a string'));

        $type->decode('just a string');
    }

    public function testCanEncodeReturnsTrueIfOneTypeCanEncode(): void
    {
        $type = $this->createUnionType();

        self::assertTrue($type->canEncode((object) ['foo' => 'bar']));
        self::assertTrue($type->canEncode(new DateTimeImmutable()));
    }

    public function testCanEncodeReturnsFalseIfNoTypeCanEncode(): void
    {
        $type = $this->createUnionType();

        self::assertFalse($type->canEncode(BSONDocument::fromPHP(['foo' => 'bar'])));
        self::assertFalse($type->canEncode(['foo' => 'bar']));
        self::assertFalse($type->canEncode('just a string'));
    }

    public function testEncodeWithStdClass(): void
    {
        $type = $this->createUnionType();

        $result = $type->encode((object) ['foo' => 'bar']);

        self::assertEquals(BSONDocument::fromPHP(['foo' => 'bar']), $result);
    }

    public function testEncodeWithDateTimeImmutable(): void
    {
        $type = $this->createUnionType();

        $dateTimeImmutable = new DateTimeImmutable();
        $result = $type->encode($dateTimeImmutable);

        self::assertInstanceOf(UTCDateTime::class, $result);
        self::assertEquals(new UTCDateTime($dateTimeImmutable), $result);
    }

    public function testEncodeWithUnsupportedValue(): void
    {
        $type = $this->createUnionType();

        $this->expectExceptionObject(UnsupportedValueException::invalidEncodableValue('just a string'));

        $type->encode('just a string');
    }

    /** Creates a union type that accepts an object or a UTC Date Time */
    private function createUnionType(): Union
    {
        return new Union(
            new ObjectType(),
            new DateTime(),
        );
    }
}
