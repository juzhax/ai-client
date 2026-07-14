<?php

declare(strict_types=1);

namespace Juzhax\AiClient\Resources;

use Juzhax\AiClient\Requests\WorkflowRunRequest;
use Juzhax\AiClient\Responses\WorkflowRunResponse;
use Juzhax\AiClient\Support\ResponseMapper;
use Juzhax\AiClient\Support\Transport;

final readonly class WorkflowResource
{
    public function __construct(private Transport $transport, private ResponseMapper $mapper)
    {
    }

    public function run(string $workflow, WorkflowRunRequest $request): WorkflowRunResponse
    {
        return $this->mapper->workflow($this->transport->send(
            'POST',
            '/v1/workflows/'.rawurlencode($workflow).'/run',
            $request->toArray(),
        ));
    }

    public function status(string $run): WorkflowRunResponse
    {
        return $this->mapper->workflow($this->transport->send(
            'GET',
            '/v1/workflows/runs/'.rawurlencode($run),
        ));
    }
}
