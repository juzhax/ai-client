<?php

declare(strict_types=1);

namespace Juzhax\AiClient\Resources;

use Juzhax\AiClient\Responses\HealthResponse;
use Juzhax\AiClient\Support\ResponseMapper;
use Juzhax\AiClient\Support\Transport;

final readonly class HealthResource
{
    public function __construct(private Transport $transport, private ResponseMapper $mapper)
    {
    }

    public function ping(): HealthResponse
    {
        return $this->mapper->health($this->transport->send('GET', '/up'));
    }
}
