<?php

declare(strict_types=1);

namespace Juzhax\AiClient\Resources;

use Juzhax\AiClient\Responses\ApplicationResponse;
use Juzhax\AiClient\Support\ResponseMapper;
use Juzhax\AiClient\Support\Transport;

final readonly class ApplicationsResource
{
    public function __construct(private Transport $transport, private ResponseMapper $mapper)
    {
    }

    public function me(): ApplicationResponse
    {
        return $this->mapper->application($this->transport->send('GET', '/v1/applications/me'));
    }
}
