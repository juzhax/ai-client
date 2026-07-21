<?php

declare(strict_types=1);

namespace Juzhax\AiClient\Requests;

use Juzhax\AiClient\Contracts\RequestData;
use Juzhax\AiClient\Data\JsonData;

final readonly class PromptRunRequest implements RequestData
{
    public function __construct(
        public string $provider,
        public string $model,
        public JsonData $variables = new JsonData(),
    ) {
    }

    public function toArray(): array
    {
        return [
            'provider' => $this->provider,
            'model' => $this->model,
            'variables' => $this->variables->value,
        ];
    }
}
