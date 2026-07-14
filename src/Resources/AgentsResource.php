<?php

declare(strict_types=1);

namespace Juzhax\AiClient\Resources;

use Juzhax\AiClient\Requests\AgentRunRequest;
use Juzhax\AiClient\Responses\AgentResponse;
use Juzhax\AiClient\Support\ResponseMapper;
use Juzhax\AiClient\Support\Transport;

final readonly class AgentsResource
{
    public function __construct(private Transport $transport, private ResponseMapper $mapper)
    {
    }

    public function run(string $agent, AgentRunRequest $request): AgentResponse
    {
        return $this->mapper->agent($this->transport->send(
            'POST',
            '/v1/agents/'.rawurlencode($agent).'/run',
            $request->toArray(),
        ));
    }
}
