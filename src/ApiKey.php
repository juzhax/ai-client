<?php

declare(strict_types=1);

namespace Juzhax\AiClient;

use InvalidArgumentException;

final readonly class ApiKey
{
    public function __construct(public string $value)
    {
        if (trim($value) === '') {
            throw new InvalidArgumentException('The API key cannot be empty.');
        }
    }
}
