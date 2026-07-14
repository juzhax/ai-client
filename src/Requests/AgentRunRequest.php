<?php

declare(strict_types=1);

namespace Juzhax\AiClient\Requests;

use Juzhax\AiClient\Contracts\RequestData;
use Juzhax\AiClient\Data\JsonData;

final readonly class AgentRunRequest implements RequestData
{
    public function __construct(public string $input, public ?JsonData $context = null)
    {
    }

    public function toArray(): array
    {
        return array_filter(
            ['input' => $this->input, 'context' => $this->context?->value],
            static fn (mixed $value): bool => $value !== null,
        );
    }
}
