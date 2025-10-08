<?php

declare(strict_types=1);

namespace Alcaeus\Tests\Metadata\Type;

use Alcaeus\Metadata\Type\DateTime;
use Alcaeus\Tests\Metadata\Fixtures\CustomDateTime;
use DateTime as NativeDateTime;
use DateTimeImmutable;
use DateTimeZone;
use MongoDB\BSON\UTCDateTime;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use stdClass;

use function date_default_timezone_get;

#[CoversClass(DateTime::class)]
class DateTimeTest extends TestCase
{
    public function testCanDecodeReturnsTrueForUTCDateTime(): void
    {
        $type = new DateTime();
        $utcDateTime = new UTCDateTime(1609459200000); // 2021-01-01 00:00:00 UTC

        self::assertTrue($type->canDecode($utcDateTime));
    }

    public function testCanDecodeReturnsFalseForNonUTCDateTime(): void
    {
        $type = new DateTime();

        self::assertFalse($type->canDecode(new NativeDateTime()));
        self::assertFalse($type->canDecode(new DateTimeImmutable()));
        self::assertFalse($type->canDecode('2021-01-01'));
        self::assertFalse($type->canDecode(1609459200));
        self::assertFalse($type->canDecode(null));
        self::assertFalse($type->canDecode([]));
    }

    public function testCanEncodeReturnsTrueForDateTimeInterface(): void
    {
        $type = new DateTime();

        self::assertTrue($type->canEncode(new NativeDateTime()));
        self::assertTrue($type->canEncode(new DateTimeImmutable()));
        self::assertTrue($type->canEncode(new CustomDateTime()));
    }

    public function testCanEncodeReturnsFalseForNonDateTimeInterface(): void
    {
        $type = new DateTime();

        self::assertFalse($type->canEncode('2021-01-01'));
        self::assertFalse($type->canEncode(1609459200));
        self::assertFalse($type->canEncode(null));
        self::assertFalse($type->canEncode([]));
        self::assertFalse($type->canEncode(new stdClass()));
    }

    public function testEncodeWithDefaultClass(): void
    {
        $type = new DateTime();
        $utcDateTime = new UTCDateTime(1609459200000); // 2021-01-01 00:00:00 UTC

        $result = $type->decode($utcDateTime);

        self::assertInstanceOf(DateTimeImmutable::class, $result);
        self::assertSame('2021-01-01 00:00:00', $result->format('Y-m-d H:i:s'));
    }

    public function testEncodeWithDateTime(): void
    {
        $type = new DateTime(NativeDateTime::class);
        $utcDateTime = new UTCDateTime(1609459200000); // 2021-01-01 00:00:00 UTC

        $result = $type->decode($utcDateTime);

        self::assertInstanceOf(NativeDateTime::class, $result);
        self::assertSame('2021-01-01 00:00:00', $result->format('Y-m-d H:i:s'));
    }

    public function testEncodeWithDateTimeImmutable(): void
    {
        $type = new DateTime(DateTimeImmutable::class);
        $utcDateTime = new UTCDateTime(1609459200000); // 2021-01-01 00:00:00 UTC

        $result = $type->decode($utcDateTime);

        self::assertInstanceOf(DateTimeImmutable::class, $result);
        self::assertSame('2021-01-01 00:00:00', $result->format('Y-m-d H:i:s'));
    }

    public function testConstructorWithCustomDateTimeClass(): void
    {
        $type = new DateTime(CustomDateTime::class);
        $utcDateTime = new UTCDateTime(1609459200000); // 2021-01-01 00:00:00 UTC

        $result = $type->decode($utcDateTime);

        self::assertInstanceOf(CustomDateTime::class, $result);
        self::assertSame('2021-01-01 00:00:00 [Custom]', $result->format('Y-m-d H:i:s'));
    }

    public function testEncodeNativeDateTimeToUTCDateTime(): void
    {
        $type = new DateTime();
        $dateTime = new NativeDateTime('2021-01-01 12:30:45', new DateTimeZone('UTC'));

        $result = $type->encode($dateTime);

        self::assertInstanceOf(UTCDateTime::class, $result);
        // Compare the actual timestamps by converting to DateTime and checking the timestamp
        self::assertSame($dateTime->getTimestamp(), $result->toDateTime()->getTimestamp());
    }

    public function testEncodeDateTimeImmutableToUTCDateTime(): void
    {
        $type = new DateTime();
        $dateTime = new DateTimeImmutable('2021-01-01 12:30:45', new DateTimeZone('UTC'));

        $result = $type->encode($dateTime);

        self::assertInstanceOf(UTCDateTime::class, $result);
        self::assertSame($dateTime->getTimestamp(), $result->toDateTime()->getTimestamp());
    }

    public function testEncodeCustomDateTimeToUTCDateTime(): void
    {
        $type = new DateTime();
        $dateTime = new CustomDateTime('2021-01-01 12:30:45', new DateTimeZone('UTC'));

        $result = $type->encode($dateTime);

        self::assertInstanceOf(UTCDateTime::class, $result);
        self::assertSame($dateTime->getTimestamp(), $result->toDateTime()->getTimestamp());
    }

    public function testDecodeWithMillisecondPrecision(): void
    {
        $type = new DateTime();
        // 1609459200123 = 2021-01-01 00:00:00.123 UTC
        $utcDateTime = new UTCDateTime(1609459200123);

        $result = $type->decode($utcDateTime);

        self::assertSame('2021-01-01 00:00:00', $result->format('Y-m-d H:i:s'));
        // Check milliseconds are preserved
        $milliseconds = (int) $result->format('v');
        self::assertSame(123, $milliseconds);
    }

    public function testEncodeWithMinimumTimestamp(): void
    {
        $type = new DateTime();
        // Test with a very early date
        $dateTime = new DateTimeImmutable('1970-01-01 00:00:01', new DateTimeZone('UTC'));

        $result = $type->encode($dateTime);

        self::assertInstanceOf(UTCDateTime::class, $result);
        self::assertSame(1000, $result->toDateTime()->getTimestamp() * 1000);
    }

    public function testDecodeUsesDefaultSystemTimezone(): void
    {
        $type = new DateTime();
        // Create UTCDateTime - it's always in UTC
        $utcDateTime = new UTCDateTime(1609459200000);

        $result = $type->decode($utcDateTime);

        self::assertSame(date_default_timezone_get(), $result->getTimezone()->getName());
    }
}
