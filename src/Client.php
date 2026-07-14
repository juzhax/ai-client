<?php

declare(strict_types=1);

namespace Juzhax\AiClient;

use Juzhax\AiClient\Resources\AgentsResource;
use Juzhax\AiClient\Resources\ApplicationsResource;
use Juzhax\AiClient\Resources\HealthResource;
use Juzhax\AiClient\Resources\PromptsResource;
use Juzhax\AiClient\Resources\RequestResource;
use Juzhax\AiClient\Resources\WorkflowResource;
use Juzhax\AiClient\Support\ResponseMapper;
use Juzhax\AiClient\Support\Transport;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

final class Client
{
    private readonly Transport $transport;
    private readonly ResponseMapper $mapper;

    public function __construct(
        Config $config,
        ClientInterface $httpClient,
        RequestFactoryInterface $requestFactory,
        StreamFactoryInterface $streamFactory,
    ) {
        $this->transport = new Transport($httpClient, $requestFactory, $streamFactory, $config);
        $this->mapper = new ResponseMapper();
    }

    public function health(): HealthResource
    {
        return new HealthResource($this->transport, $this->mapper);
    }

    public function applications(): ApplicationsResource
    {
        return new ApplicationsResource($this->transport, $this->mapper);
    }

    public function agents(): AgentsResource
    {
        return new AgentsResource($this->transport, $this->mapper);
    }

    public function prompts(): PromptsResource
    {
        return new PromptsResource($this->transport, $this->mapper);
    }

    public function workflows(): WorkflowResource
    {
        return new WorkflowResource($this->transport, $this->mapper);
    }

    public function requests(): RequestResource
    {
        return new RequestResource($this->transport, $this->mapper);
    }
}
