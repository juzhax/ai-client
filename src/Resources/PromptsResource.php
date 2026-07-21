<?php

declare(strict_types=1);

namespace Juzhax\AiClient\Resources;

use Juzhax\AiClient\Requests\PromptRenderRequest;
use Juzhax\AiClient\Requests\PromptRunRequest;
use Juzhax\AiClient\Responses\PromptResponse;
use Juzhax\AiClient\Responses\PromptRunResponse;
use Juzhax\AiClient\Support\ResponseMapper;
use Juzhax\AiClient\Support\Transport;

final readonly class PromptsResource
{
    public function __construct(private Transport $transport, private ResponseMapper $mapper)
    {
    }

    public function render(string $prompt, PromptRenderRequest $request): PromptResponse
    {
        return $this->mapper->prompt($this->transport->send(
            'POST',
            '/v1/prompts/'.rawurlencode($prompt).'/render',
            $request->toArray(),
        ));
    }

    public function run(string $prompt, PromptRunRequest $request): PromptRunResponse
    {
        return $this->mapper->promptRun($this->transport->send(
            'POST',
            '/api/v1/prompts/'.rawurlencode($prompt).'/runs',
            $request->toArray(),
        ));
    }
}
