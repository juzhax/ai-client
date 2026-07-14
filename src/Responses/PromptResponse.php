<?php

declare(strict_types=1);

namespace Juzhax\AiClient\Responses;

use Juzhax\AiClient\Data\JsonData;

final readonly class PromptResponse
{
    public function __construct(public string $content, public JsonData $data)
    {
    }
}
