<?php

declare(strict_types=1);

// Note: properties in this class are non-promoted as promoted properties are not initialised when creating a new
// instance without invoking the constructor
// phpcs:disable SlevomatCodingStandard.Classes.RequireConstructorPropertyPromotion.RequiredConstructorPropertyPromotion

namespace Alcaeus\Tests\Metadata\Type\Fixtures;

class TestClass
{
    public mixed $foo = null;
    public mixed $bar = null;
    public mixed $baz = null;

    public function __construct(
        mixed $foo = null,
        mixed $bar = null,
        mixed $baz = null,
    ) {
        $this->foo = $foo;
        $this->bar = $bar;
        $this->baz = $baz;
    }
}
