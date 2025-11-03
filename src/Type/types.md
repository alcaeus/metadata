# Type System Reference

Types are automatically inferred when reading metadata, but are customisable. In general:
- if an attribute accepts a `type` argument, you can pass any Alcaeus\Metadata\Type\Type instance
- if said `type` attribute is left empty, mapped fields will make a best-effort guess based on type information and PHPDoc.

## Examples

```php
private string $withNativeType;
/** @var string */
private $withPhpDocType;
```

Scalar types result in a hard type cast when writing to and reading from the database. Any warnings or errors during casting are left for the user to deal with. An "empty" (e.g. an empty string) value will still be written to the database. If the field is not present in the database, it will not be set on the object.

```php
private ?string $nullableString;
```

Nullable types have special handling. If the value is `null`, the field will not be set in the database. When reading from the database, a non-existant field will be treated as a `null` value on the object.

```php
private array $array;
```

An array will be treated as a hash map. In order to store a list, the array needs to be documented as `list` in PHPDoc.

```php
private object $object;
```

An object will be serialised to BSON when writing to the database. Only public properties will be stored in the database. For any classes other than `\stdClass`, reading the value will result in a `\stdClass` object. To map to a specific class, please map a document and embed it.

```php
private SomeDocument $document;
```

When mapping documents, you need to map the field either as `Embedded` or as `Reference`, depending on how you want to store the result.

Union types

## Type Guessing

The type system uses symfony/type-info to get information about the type declared on a property. This first reads the type declaration, then the PHPDoc type and returns information.

Union types and intersection types are not supported. Nullable types still need discussing.

### `Field` Attribute

The `field` attributes defaults to a `Raw` type, which means no transformation is done. If the native type hint is `array`, the type will be guessed as `Hash` (a map of string to mixed values). To store a list, the array needs to be documented as `list<...>` or `array<int, ...>` in PHPDoc. Note that the value type is still always `raw`.
