<?php

declare(strict_types=1);

namespace Juzhax\AiClient\Responses;

use Juzhax\AiClient\Data\JsonData;

final readonly class PromptRunResponse
{
    public function __construct(
        public string $requestUuid,
        public string $output,
        public ?JsonData $structured,
        public string $provider,
        public string $model,
        public JsonData $usage,
        public JsonData $cost,
        public ?string $finishReason,
        public ?int $durationMs,
        public JsonData $data,
    ) {
    }
}
