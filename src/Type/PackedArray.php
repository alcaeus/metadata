<?php

declare(strict_types=1);

namespace Alcaeus\Metadata\Type;

use MongoDB\BSON\PackedArray as BSONPackedArray;
use MongoDB\Codec\DecodeIfSupported;
use MongoDB\Codec\EncodeIfSupported;

use function array_map;
use function array_values;
use function is_array;

/**
 * @template ItemBSONType
 * @template ItemNativeType
 * @template-implements Type<BSONPackedArray, array>
 */
final class PackedArray implements Type
{
    /** @use DecodeIfSupported<BSONPackedArray, array> */
    use DecodeIfSupported;
    /** @use EncodeIfSupported<BSONPackedArray, array> */
    use EncodeIfSupported;

    // phpcs:disable
    public ?Type $wrappedType {
        get => $this->itemType;
    }
    // phpcs:enable

    /** @param Type<ItemBSONType, ItemNativeType>|null $itemType */
    public function __construct(private readonly ?Type $itemType = null)
    {
    }

    public function canDecode(mixed $value): bool
    {
        return $value instanceof BSONPackedArray;
    }

    public function canEncode(mixed $value): bool
    {
        return is_array($value);
    }

    /**
     * @param BSONPackedArray<ItemBSONType> $value
     *
     * @return list<ItemNativeType>
     */
    public function decode(mixed $value): array
    {
        $items = [];

        foreach ($value as $item) {
            $items[] = $this->itemType ? $this->itemType->decode($item) : $item;
        }

        return $items;
    }

    /**
     * @param list<ItemNativeType> $value
     *
     * @return BSONPackedArray<ItemBSONType>
     */
    public function encode(mixed $value): BSONPackedArray
    {
        if ($this->itemType) {
            $value = array_map(
                $this->itemType->encode(...),
                $value,
            );
        }

        return BSONPackedArray::fromPHP(array_values($value));
    }
}
