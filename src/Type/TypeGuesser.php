<?php

declare(strict_types=1);

namespace Alcaeus\Metadata\Type;

use Alcaeus\Metadata\DocumentMetadataStore;
use DateTime as NativeDateTime;
use DateTimeImmutable;
use InvalidArgumentException;
use Symfony\Component\TypeInfo\Type as TypeInfo;
use Symfony\Component\TypeInfo\TypeIdentifier;

use function array_map;
use function sprintf;

final class TypeGuesser
{
    public static function guessType(TypeInfo $type, DocumentMetadataStore $store): Type
    {
        return match (true) {
            $type instanceof TypeInfo\UnionType => static::createUnionType($store, $type),
            $type instanceof TypeInfo\IntersectionType =>
                throw new InvalidArgumentException('Intersection types are not supported'),
            $type instanceof TypeInfo\ObjectType => static::createObjectType($type, $store),
            $type instanceof TypeInfo\BuiltinType => static::createBuiltinType($type, $store),
            $type instanceof TypeInfo\CollectionType => static::createCollectionType($type, $store),
            default => new Raw(),
        };
    }

    private static function createBuiltinType(TypeInfo\BuiltinType $type, DocumentMetadataStore $store): Type
    {
        return match ($type->getTypeIdentifier()) {
            TypeIdentifier::ARRAY => new Hash(new Raw()),
            TypeIdentifier::OBJECT => new ObjectType(),
            TypeIdentifier::BOOL => new BoolType(),
            TypeIdentifier::FLOAT => new FloatType(),
            TypeIdentifier::INT => new IntType(),
            TypeIdentifier::STRING => new StringType(),
            TypeIdentifier::NULL => new Raw(),
            TypeIdentifier::MIXED => new Raw(),
            default => throw new InvalidArgumentException(sprintf('Type with identifier "%s" is not supported.', $type->getTypeIdentifier()->value)),
        };
    }

    private static function createCollectionType(TypeInfo\CollectionType $type, DocumentMetadataStore $store): Type
    {
        $nestedType = static::guessType($type->getCollectionValueType(), $store);

        return $type->isList()
            ? new PackedArray($nestedType)
            : new Hash($nestedType);
    }

    private static function createObjectType(TypeInfo\ObjectType $type, DocumentMetadataStore $store): Type
    {
        if ($type->isIdentifiedBy(DateTimeImmutable::class) || $type->isIdentifiedBy(NativeDateTime::class)) {
            return new DateTime($type->getClassName());
        }

        $documentMetadata = $store->tryGetMetadata($type->getClassName());
        if ($documentMetadata !== null) {
            return new Document($documentMetadata);
        }

        return new ObjectType($type->getClassName());
    }

    private static function createUnionType(DocumentMetadataStore $store, TypeInfo\UnionType $type): Union
    {
        return new Union(
            ...array_map(
                static fn (TypeInfo $innerType) => static::guessType($innerType, $store),
                $type->getTypes(),
            ),
        );
    }
}
