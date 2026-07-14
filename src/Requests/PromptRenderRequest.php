<?php

declare(strict_types=1);

namespace Juzhax\AiClient\Requests;

use Juzhax\AiClient\Contracts\RequestData;
use Juzhax\AiClient\Data\JsonData;

final readonly class PromptRenderRequest implements RequestData
{
    public function __construct(public JsonData $variables = new JsonData())
    {
    }

    public function toArray(): array
    {
        return ['variables' => $this->variables->value];
    }
}
