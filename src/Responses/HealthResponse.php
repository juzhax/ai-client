<?php

declare(strict_types=1);

namespace Juzhax\AiClient\Responses;

use Juzhax\AiClient\Data\JsonData;

final readonly class HealthResponse
{
    public function __construct(public string $status, public JsonData $data)
    {
    }
}
