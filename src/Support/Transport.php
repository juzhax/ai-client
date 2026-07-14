<?php

declare(strict_types=1);

namespace Juzhax\AiClient\Support;

use JsonException;
use Juzhax\AiClient\Config;
use Juzhax\AiClient\Data\JsonData;
use Juzhax\AiClient\Exception\AiException;
use Juzhax\AiClient\Exception\ApiException;
use Juzhax\AiClient\Exception\AuthenticationException;
use Juzhax\AiClient\Exception\NetworkException;
use Juzhax\AiClient\Exception\RateLimitException;
use Juzhax\AiClient\Exception\ValidationException;
use Juzhax\AiClient\Responses\ApiErrorResponse;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use stdClass;
use Throwable;

final class Transport
{
    /** @var list<int> */
    private const RETRYABLE_STATUS_CODES = [429, 502, 503, 504];

    public function __construct(
        private ClientInterface $httpClient,
        private RequestFactoryInterface $requestFactory,
        private StreamFactoryInterface $streamFactory,
        private Config $config,
    ) {
    }

    /** @param array<string, mixed>|null $payload */
    public function send(string $method, string $path, ?array $payload = null): stdClass
    {
        $request = $this->requestFactory->createRequest(
            $method,
            rtrim($this->config->baseUrl, '/').'/'.ltrim($path, '/'),
        )
            ->withHeader('Authorization', 'Bearer '.$this->config->apiKey->value)
            ->withHeader('Accept', 'application/json')
            ->withHeader('Content-Type', 'application/json')
            ->withHeader('User-Agent', $this->config->userAgent);

        if ($payload !== null) {
            try {
                $body = json_encode($payload, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);
            } catch (JsonException $exception) {
                throw new AiException('The request payload could not be encoded.', previous: $exception);
            }
            $request = $request->withBody($this->streamFactory->createStream($body));
        }

        $attempt = 0;
        do {
            try {
                if ($request->getBody()->isSeekable()) {
                    $request->getBody()->rewind();
                }
                $response = $this->httpClient->sendRequest($request);
            } catch (ClientExceptionInterface $exception) {
                throw new NetworkException('The AI Platform request failed.', previous: $exception);
            } catch (Throwable $exception) {
                throw new NetworkException('The HTTP client failed unexpectedly.', previous: $exception);
            }

            $status = $response->getStatusCode();
            if (!in_array($status, self::RETRYABLE_STATUS_CODES, true) || $attempt >= $this->config->retry) {
                break;
            }
            ++$attempt;
        } while (true);

        $raw = (string) $response->getBody();
        $data = $this->decode($raw);

        if ($status < 200 || $status >= 300) {
            throw $this->exception($status, $data);
        }

        return $this->unwrap($data);
    }

    private function decode(string $raw): stdClass
    {
        if ($raw === '') {
            return new stdClass();
        }

        try {
            $data = json_decode($raw, false, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new AiException('The AI Platform returned invalid JSON.', previous: $exception);
        }

        if (!$data instanceof stdClass) {
            throw new AiException('The AI Platform returned an unexpected JSON document.');
        }

        return $data;
    }

    private function unwrap(stdClass $data): stdClass
    {
        return isset($data->data) && $data->data instanceof stdClass ? $data->data : $data;
    }

    private function exception(int $status, stdClass $data): ApiException
    {
        $source = isset($data->error) && $data->error instanceof stdClass ? $data->error : $data;
        $message = isset($source->message) && is_string($source->message)
            ? $source->message
            : 'The AI Platform returned HTTP '.$status.'.';
        $code = isset($source->code) && (is_string($source->code) || is_numeric($source->code))
            ? (string) $source->code
            : null;
        $error = new ApiErrorResponse($status, $message, $code, new JsonData($source));

        return match (true) {
            $status === 401, $status === 403 => new AuthenticationException($error),
            $status === 422 => new ValidationException($error),
            $status === 429 => new RateLimitException($error),
            default => new ApiException($error),
        };
    }
}
