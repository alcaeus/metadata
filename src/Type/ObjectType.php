<?php

declare(strict_types=1);

namespace Alcaeus\Metadata\Type;

use MongoDB\BSON\Document as BSONDocument;
use MongoDB\Codec\DecodeIfSupported;
use MongoDB\Codec\EncodeIfSupported;
use ReflectionClass;
use stdClass;

use function get_object_vars;

/**
 * @template T of object
 * @template-implements Type<BSONDocument, T>
 */
final readonly class ObjectType implements Type
{
    /** @use DecodeIfSupported<BSONDocument, T> */
    use DecodeIfSupported;
    /** @use EncodeIfSupported<BSONDocument, T> */
    use EncodeIfSupported;

    /** @var ReflectionClass<T> */
    private ReflectionClass $class;

    /** @param class-string<T> $className */
    public function __construct(private string $className = stdClass::class)
    {
        $this->class = new ReflectionClass($className);
    }

    public function canDecode(mixed $value): bool
    {
        return $value instanceof BSONDocument;
    }

    public function canEncode(mixed $value): bool
    {
        return $value instanceof $this->className;
    }

    /**
     * @param BSONDocument $value
     *
     * @return T
     */
    public function decode(mixed $value): object
    {
        $object = $this->class->newInstanceWithoutConstructor();
        foreach ($value as $key => $objectValue) {
            $object->$key = $objectValue;
        }

        return $object;
    }

    /** @param T $value */
    public function encode(mixed $value): BSONDocument
    {
        return BSONDocument::fromPHP(get_object_vars($value));
    }
}
