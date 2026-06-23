<?php

declare(strict_types=1);

namespace Alcaeus\Metadata\Type;

use MongoDB\BSON\Document as BSONDocument;
use MongoDB\Codec\DecodeIfSupported;
use MongoDB\Codec\EncodeIfSupported;

use function array_map;
use function is_array;

/**
 * @template ItemBSONType
 * @template ItemNativeType
 * @template-implements Type<BSONDocument, array>
 */
final class Hash implements Type, WrappingType
{
    /** @use DecodeIfSupported<BSONDocument, array> */
    use DecodeIfSupported;
    /** @use EncodeIfSupported<BSONDocument, array> */
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
        return $value instanceof BSONDocument;
    }

    public function canEncode(mixed $value): bool
    {
        return is_array($value);
    }

    /**
     * @param BSONDocument<ItemBSONType> $value
     *
     * @return list<ItemNativeType>
     */
    public function decode(mixed $value): array
    {
        $items = [];

        foreach ($value as $key => $item) {
            $items[$key] = $this->itemType ? $this->itemType->decode($item) : $item;
        }

        return $items;
    }

    /**
     * @param list<ItemNativeType> $value
     *
     * @return BSONDocument<ItemBSONType>
     */
    public function encode(mixed $value): BSONDocument
    {
        if ($this->itemType) {
            $value = array_map(
                $this->itemType->encode(...),
                $value,
            );
        }

        return BSONDocument::fromPHP($value);
    }
}
