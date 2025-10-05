<?php

declare(strict_types=1);

namespace Alcaeus\Metadata\Type;

use Closure;
use DateTime as NativeDateTime;
use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use MongoDB\BSON\UTCDateTime;
use MongoDB\Codec\DecodeIfSupported;
use MongoDB\Codec\EncodeIfSupported;

use function date_default_timezone_get;

/**
 * @template DateTimeType of NativeDateTime|DateTimeImmutable
 * @template-implements Type<UTCDateTime, DateTimeType>
 */
final readonly class DateTime implements Type
{
    /** @use DecodeIfSupported<UTCDateTime, DateTimeType> */
    use DecodeIfSupported;
    /** @use EncodeIfSupported<UTCDateTime, DateTimeType> */
    use EncodeIfSupported;

    /** @var Closure(DateTimeInterface): DateTimeType */
    private Closure $createInstance;

    /** @param class-string<DateTimeType> $dateTimeClass */
    public function __construct(private string $dateTimeClass = DateTimeImmutable::class)
    {
        $this->createInstance = $this->dateTimeClass::createFromInterface(...);
    }

    public function canDecode(mixed $value): bool
    {
        return $value instanceof UTCDateTime;
    }

    public function canEncode(mixed $value): bool
    {
        return $value instanceof DateTimeInterface;
    }

    public function decode(mixed $value): NativeDateTime|DateTimeImmutable
    {
        return $this->createInstance
            ->__invoke($value->toDateTime())
            ->setTimezone(new DateTimeZone(date_default_timezone_get()));
    }

    public function encode(mixed $value): UTCDateTime
    {
        return new UTCDateTime($value);
    }
}
