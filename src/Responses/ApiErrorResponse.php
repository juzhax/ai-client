<?php

declare(strict_types=1);

namespace Juzhax\AiClient\Responses;

use Juzhax\AiClient\Data\JsonData;

final readonly class ApiErrorResponse
{
    public function __construct(
        public int $status,
        public string $message,
        public ?string $code,
        public JsonData $details,
    ) {
    }
}
