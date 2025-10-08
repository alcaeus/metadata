<?php

declare(strict_types=1);

namespace Alcaeus\Tests\Metadata\Type;

use Alcaeus\Metadata\DocumentMetadata;
use Alcaeus\Metadata\DocumentMetadataStore;
use Alcaeus\Metadata\Loader\AttributeLoader;
use Alcaeus\Metadata\Type\Document;
use Alcaeus\Tests\Metadata\Fixtures\TestDocumentA;
use MongoDB\BSON\Document as BSONDocument;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use stdClass;

#[CoversClass(Document::class)]
class DocumentTest extends TestCase
{
    private Document $documentType;
    private DocumentMetadata $metadata;

    protected function setUp(): void
    {
        $this->metadata = $this->getMetadataForClass(TestDocumentA::class);
        $this->documentType = new Document($this->metadata);
    }

    private function getMetadataForClass(string $className): DocumentMetadata
    {
        return (new AttributeLoader())->load($className, new DocumentMetadataStore());
    }

    public function testCanDecodeReturnsTrueForBSONDocument(): void
    {
        $bsonDoc = BSONDocument::fromPHP([]);

        self::assertTrue($this->documentType->canDecode($bsonDoc));
    }

    public function testCanDecodeReturnsFalseForNonBSONDocument(): void
    {
        self::assertFalse($this->documentType->canDecode(['_id' => 1]));
        self::assertFalse($this->documentType->canDecode('string'));
        self::assertFalse($this->documentType->canDecode(123));
        self::assertFalse($this->documentType->canDecode(null));
    }

    public function testCanEncodeReturnsTrueForCorrectDocumentType(): void
    {
        $document = new TestDocumentA(1, 'John Doe');

        self::assertTrue($this->documentType->canEncode($document));
    }

    public function testCanEncodeReturnsFalseForWrongType(): void
    {
        self::assertFalse($this->documentType->canEncode(new stdClass()));
        self::assertFalse($this->documentType->canEncode('string'));
        self::assertFalse($this->documentType->canEncode(123));
        self::assertFalse($this->documentType->canEncode([]));
    }

    public function testEncodeConvertsObjectToBSONDocument(): void
    {
        $document = new TestDocumentA(42, 'Jane Smith');

        $result = $this->documentType->encode($document);

        self::assertEquals(
            (object) [
                '_id' => 42,
                'fullName' => 'Jane Smith',
                'notes' => '',
            ],
            $result->toPHP(),
        );
    }

    public function testDecodeCreatesLazyGhostObject(): void
    {
        // Intentionally only include an identifier; this shouldn't cause errors as we're not accessing fields.
        $bsonDoc = BSONDocument::fromPHP(['_id' => 123]);

        $result = $this->documentType->decode($bsonDoc);

        self::assertInstanceOf(TestDocumentA::class, $result);

        // Verify it's a lazy ghost using reflection
        $reflection = new ReflectionClass($result);
        self::assertTrue($reflection->isUninitializedLazyObject($result));
    }

    public function testIdentifierIsInitializedImmediately(): void
    {
        $bsonDoc = BSONDocument::fromPHP([
            '_id' => 456,
            'fullName' => 'Alice Johnson',
            'notes' => 'lazy loading test',
        ]);

        $result = $this->documentType->decode($bsonDoc);

        self::assertInstanceOf(TestDocumentA::class, $result);

        // Accessing the identifier should not trigger full initialization
        $reflection = new ReflectionClass($result);
        self::assertTrue($reflection->isUninitializedLazyObject($result));

        // The identifier should be accessible immediately
        self::assertSame(456, $result->id);

        // After accessing the identifier, object should still be a lazy ghost
        self::assertTrue($reflection->isUninitializedLazyObject($result));
    }

    public function testAccessingNonIdentifierFieldTriggersInitialization(): void
    {
        $bsonDoc = BSONDocument::fromPHP([
            '_id' => 789,
            'fullName' => 'Charlie Brown',
            'notes' => 'initialization test',
        ]);

        $result = $this->documentType->decode($bsonDoc);

        self::assertInstanceOf(TestDocumentA::class, $result);

        // Verify it starts as a lazy ghost
        $reflection = new ReflectionClass($result);
        self::assertTrue($reflection->isUninitializedLazyObject($result));

        // Check Object should no longer be a lazy ghost
        self::assertSame('Charlie Brown', $result->name);
        self::assertSame('initialization test', $result->notes);
        self::assertFalse($reflection->isUninitializedLazyObject($result));
    }

    public function testDecodeHandlesMissingOptionalFields(): void
    {
        $bsonDoc = BSONDocument::fromPHP([
            '_id' => 111,
            'fullName' => 'Eve Green',
            // notes field is missing
        ]);

        $result = $this->documentType->decode($bsonDoc);

        self::assertInstanceOf(TestDocumentA::class, $result);

        self::assertSame('', $result->notes);
    }

    public function testDecodeIgnoresUnknownFields(): void
    {
        $bsonDoc = BSONDocument::fromPHP([
            '_id' => 222,
            'fullName' => 'Frank Miller',
            'notes' => 'unknown fields test',
            'unknownField' => 'should be ignored',
        ]);

        $result = $this->documentType->decode($bsonDoc);

        self::assertSame(222, $result->id);
        self::assertSame('Frank Miller', $result->name);
        self::assertSame('unknown fields test', $result->notes);
    }

    public function testEncodeDecodeRoundTrip(): void
    {
        $original = new TestDocumentA(555, 'Grace Thompson');
        $original->notes = 'round trip test';

        // Encode to BSON
        $bsonDoc = $this->documentType->encode($original);

        // Decode back to object
        $decoded = $this->documentType->decode($bsonDoc);

        // Force initialisation of lazy object, comparison will fail otherwise
        new ReflectionClass($decoded)->initializeLazyObject($decoded);

        self::assertEquals($original, $decoded);
    }
}
