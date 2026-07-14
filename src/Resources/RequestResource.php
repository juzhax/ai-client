<?php

declare(strict_types=1);

namespace Juzhax\AiClient\Resources;

use Juzhax\AiClient\Responses\RequestResponse;
use Juzhax\AiClient\Support\ResponseMapper;
use Juzhax\AiClient\Support\Transport;

final readonly class RequestResource
{
    public function __construct(private Transport $transport, private ResponseMapper $mapper)
    {
    }

    public function find(string $request): RequestResponse
    {
        return $this->mapper->request($this->transport->send(
            'GET',
            '/v1/requests/'.rawurlencode($request),
        ));
    }
}
