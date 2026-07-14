<?php

declare(strict_types=1);

namespace Juzhax\AiClient\Requests;

use Juzhax\AiClient\Contracts\RequestData;
use Juzhax\AiClient\Data\JsonData;

final readonly class WorkflowRunRequest implements RequestData
{
    public function __construct(public JsonData $input = new JsonData())
    {
    }

    public function toArray(): array
    {
        return ['input' => $this->input->value];
    }
}
