<?php

declare(strict_types=1);

namespace Juzhax\AiClient\Data;

use JsonSerializable;
use stdClass;

/** An immutable wrapper for provider-defined JSON fields. */
final readonly class JsonData implements JsonSerializable
{
    public function __construct(public stdClass $value = new stdClass())
    {
    }

    public function jsonSerialize(): stdClass
    {
        return $this->value;
    }
}
