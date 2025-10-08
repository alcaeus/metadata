<?php

declare(strict_types=1);

namespace Alcaeus\Tests\Metadata\Type;

use Alcaeus\Metadata\CollectionMetadata;
use Alcaeus\Metadata\DocumentMetadata;
use Alcaeus\Metadata\DocumentMetadataStore;
use Alcaeus\Metadata\Loader\AttributeLoader;
use Alcaeus\Metadata\Type\Reference\Id;
use Alcaeus\Metadata\Type\Reference\Reference;
use Alcaeus\Tests\Metadata\Fixtures\TestDocumentA;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use stdClass;

#[CoversClass(Id::class)]
#[CoversClass(Reference::class)]
class IdTest extends TestCase
{
    private Id $idReferenceType;
    private CollectionMetadata $metadata;

    protected function setUp(): void
    {
        $this->metadata = new CollectionMetadata('test', $this->getMetadataForClass(TestDocumentA::class));
        $this->idReferenceType = new Id($this->metadata);
    }

    private function getMetadataForClass(string $className): DocumentMetadata
    {
        return (new AttributeLoader())->load($className, new DocumentMetadataStore());
    }

    public function testCanEncodeReturnsTrueForCorrectDocumentType(): void
    {
        $document = new TestDocumentA(1, 'John Doe');

        self::assertTrue($this->idReferenceType->canEncode($document));
    }

    public function testCanEncodeReturnsFalseForWrongType(): void
    {
        self::assertFalse($this->idReferenceType->canEncode(new stdClass()));
        self::assertFalse($this->idReferenceType->canEncode('string'));
        self::assertFalse($this->idReferenceType->canEncode(123));
        self::assertFalse($this->idReferenceType->canEncode([]));
    }

    public function testEncodeConvertsObjectToId(): void
    {
        $document = new TestDocumentA(42, 'Jane Smith');

        self::assertEquals(
            42,
            $this->idReferenceType->encode($document),
        );
    }

    public function testDecodeCreatesLazyGhostObject(): void
    {
        $result = $this->idReferenceType->decode(123);

        self::assertInstanceOf(TestDocumentA::class, $result);

        // Verify it's a lazy ghost using reflection
        $reflection = new ReflectionClass($result);
        self::assertTrue($reflection->isUninitializedLazyObject($result));
    }

    public function testIdentifierIsInitializedImmediately(): void
    {
        $result = $this->idReferenceType->decode(123);

        self::assertInstanceOf(TestDocumentA::class, $result);

        // Accessing the identifier should not trigger full initialization
        $reflection = new ReflectionClass($result);
        self::assertTrue($reflection->isUninitializedLazyObject($result));

        // The identifier should be accessible immediately
        self::assertSame(123, $result->id);

        // After accessing the identifier, object should still be a lazy ghost
        self::assertTrue($reflection->isUninitializedLazyObject($result));
    }

    public function testAccessingNonIdentifierFieldTriggersInitialization(): void
    {
        $this->markTestSkipped('Not implemented');

        $result = $this->idReferenceType->decode(123);

        self::assertInstanceOf(TestDocumentA::class, $result);

        // Verify it starts as a lazy ghost
        $reflection = new ReflectionClass($result);
        self::assertTrue($reflection->isUninitializedLazyObject($result));

        // Check Object should no longer be a lazy ghost
        self::assertSame('Charlie Brown', $result->name);
        self::assertSame('initialization test', $result->notes);
        self::assertFalse($reflection->isUninitializedLazyObject($result));
    }
}
