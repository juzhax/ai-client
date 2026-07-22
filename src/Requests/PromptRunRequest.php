<?php

declare(strict_types=1);

namespace Juzhax\AiClient\Requests;

use Juzhax\AiClient\Contracts\RequestData;
use Juzhax\AiClient\Data\JsonData;

final readonly class PromptRunRequest implements RequestData
{
    public function __construct(
        public ?string $provider = null,
        public ?string $model = null,
        public JsonData $variables = new JsonData(),
    ) {
    }

    public function toArray(): array
    {
        return array_filter([
            'provider' => $this->provider,
            'model' => $this->model,
            'variables' => $this->variables->value,
        ], static fn (mixed $value): bool => $value !== null);
    }
}
