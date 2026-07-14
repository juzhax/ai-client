<?php

declare(strict_types=1);

namespace Juzhax\AiClient\Tests\Support;

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;

final class MockHttpClient implements ClientInterface
{
    /** @var list<ResponseInterface> */
    private array $responses;

    /** @var list<RequestInterface> */
    public array $requests = [];

    public function __construct(ResponseInterface ...$responses)
    {
        $this->responses = $responses;
    }

    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        $this->requests[] = $request;

        return array_shift($this->responses) ?? throw new RuntimeException('No mock response queued.');
    }
}
