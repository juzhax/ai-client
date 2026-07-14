<?php

declare(strict_types=1);

namespace Juzhax\AiClient\Responses;

use Juzhax\AiClient\Data\JsonData;

final readonly class AgentResponse
{
    public function __construct(public string $id, public ?string $output, public JsonData $data)
    {
    }
}
