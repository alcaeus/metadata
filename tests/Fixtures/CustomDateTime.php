<?php

declare(strict_types=1);

namespace Alcaeus\Tests\Metadata\Fixtures;

use DateTime as BaseDateTime;

/**
 * Custom DateTime class that extends the base DateTime for testing purposes
 */
class CustomDateTime extends BaseDateTime
{
    public function format(string $format): string
    {
        return parent::format($format) . ' [Custom]';
    }
}
