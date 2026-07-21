<?php

declare(strict_types=1);

namespace Juzhax\AiClient\Tests;

use GuzzleHttp\Psr7\HttpFactory;
use GuzzleHttp\Psr7\Response;
use Juzhax\AiClient\ApiKey;
use Juzhax\AiClient\Client;
use Juzhax\AiClient\Config;
use Juzhax\AiClient\Data\JsonData;
use Juzhax\AiClient\Exception\AuthenticationException;
use Juzhax\AiClient\Exception\RateLimitException;
use Juzhax\AiClient\Exception\ValidationException;
use Juzhax\AiClient\Requests\AgentRunRequest;
use Juzhax\AiClient\Requests\PromptRenderRequest;
use Juzhax\AiClient\Requests\PromptRunRequest;
use Juzhax\AiClient\Requests\WorkflowRunRequest;
use Juzhax\AiClient\Tests\Support\MockHttpClient;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use stdClass;

final class ClientTest extends TestCase
{
    public function testHealthRequestHasExpectedUriAndHeaders(): void
    {
        $http = new MockHttpClient(new Response(200, [], '{"data":{"status":"ok"}}'));
        $response = $this->client($http)->health()->ping();

        self::assertSame('ok', $response->status);
        self::assertCount(1, $http->requests);
        self::assertSame('GET', $http->requests[0]->getMethod());
        self::assertSame('https://ai.justin.my/up', (string) $http->requests[0]->getUri());
        self::assertSame('Bearer ai_live_test', $http->requests[0]->getHeaderLine('Authorization'));
        self::assertSame('application/json', $http->requests[0]->getHeaderLine('Accept'));
        self::assertSame('test-suite', $http->requests[0]->getHeaderLine('User-Agent'));
    }

    public function testAgentRunSerializesRequestAndReturnsDto(): void
    {
        $http = new MockHttpClient(new Response(200, [], '{"data":{"id":"run_1","output":"Hello"}}'));
        $context = new stdClass();
        $context->locale = 'en';

        $response = $this->client($http)->agents()->run(
            'support agent',
            new AgentRunRequest('Hi', new JsonData($context)),
        );

        self::assertSame('run_1', $response->id);
        self::assertSame('Hello', $response->output);
        self::assertSame('/v1/agents/support%20agent/run', $http->requests[0]->getUri()->getPath());
        self::assertJsonStringEqualsJsonString(
            '{"input":"Hi","context":{"locale":"en"}}',
            (string) $http->requests[0]->getBody(),
        );
    }

    public function testRetriesOnlyRetryableStatusCodes(): void
    {
        $http = new MockHttpClient(
            new Response(503, [], '{"message":"Unavailable"}'),
            new Response(429, [], '{"message":"Slow down"}'),
            new Response(200, [], '{"status":"ok"}'),
        );

        self::assertSame('ok', $this->client($http)->health()->ping()->status);
        self::assertCount(3, $http->requests);
    }

    public function testPromptRunUsesPlatformRouteAndMapsExecutionResponse(): void
    {
        $http = new MockHttpClient(new Response(200, [], json_encode([
            'data' => [
                'request_uuid' => '019prompt-run',
                'prompt' => ['slug' => 'explain-topic', 'version' => 2],
                'output' => 'Laravel queues process work asynchronously.',
                'structured' => ['summary' => 'Queues run work later.'],
                'provider' => 'openai',
                'model' => 'gpt-5-mini',
                'usage' => ['input_tokens' => 20, 'output_tokens' => 8, 'total_tokens' => 28],
                'cost' => ['total' => '0.00010000', 'currency' => 'USD'],
                'finish_reason' => 'stop',
                'duration_ms' => 125,
                'warnings' => [],
            ],
        ], JSON_THROW_ON_ERROR)));
        $variables = new stdClass();
        $variables->topic = 'Laravel queues';

        $response = $this->client($http)->prompts()->run(
            'explain topic',
            new PromptRunRequest('openai', 'gpt-5-mini', new JsonData($variables)),
        );

        self::assertSame('019prompt-run', $response->requestUuid);
        self::assertSame('Laravel queues process work asynchronously.', $response->output);
        self::assertSame('Queues run work later.', $response->structured?->value->summary);
        self::assertSame('openai', $response->provider);
        self::assertSame('gpt-5-mini', $response->model);
        self::assertSame(28, $response->usage->value->total_tokens);
        self::assertSame('0.00010000', $response->cost->value->total);
        self::assertSame('stop', $response->finishReason);
        self::assertSame(125, $response->durationMs);
        self::assertSame('/api/v1/prompts/explain%20topic/runs', $http->requests[0]->getUri()->getPath());
        self::assertJsonStringEqualsJsonString(
            '{"provider":"openai","model":"gpt-5-mini","variables":{"topic":"Laravel queues"}}',
            (string) $http->requests[0]->getBody(),
        );
    }

    public function testAllRemainingResourcesUseTheirDocumentedRoutes(): void
    {
        $http = new MockHttpClient(
            new Response(200, [], '{"data":{"id":"app_1","name":"Demo"}}'),
            new Response(200, [], '{"data":{"content":"Hello Ada"}}'),
            new Response(200, [], '{"data":{"id":"run_1","status":"queued"}}'),
            new Response(200, [], '{"data":{"id":"run_1","status":"complete"}}'),
            new Response(200, [], '{"data":{"id":"req_1","status":"complete"}}'),
        );
        $client = $this->client($http);

        self::assertSame('app_1', $client->applications()->me()->id);
        self::assertSame('Hello Ada', $client->prompts()->render('welcome', new PromptRenderRequest())->content);
        self::assertSame('queued', $client->workflows()->run('onboarding', new WorkflowRunRequest())->status);
        self::assertSame('complete', $client->workflows()->status('run_1')->status);
        self::assertSame('req_1', $client->requests()->find('req_1')->id);

        self::assertSame('/v1/applications/me', $http->requests[0]->getUri()->getPath());
        self::assertSame('/v1/prompts/welcome/render', $http->requests[1]->getUri()->getPath());
        self::assertSame('/v1/workflows/onboarding/run', $http->requests[2]->getUri()->getPath());
        self::assertSame('/v1/workflows/runs/run_1', $http->requests[3]->getUri()->getPath());
        self::assertSame('/v1/requests/req_1', $http->requests[4]->getUri()->getPath());
    }

    /** @return iterable<string, array{int, class-string}> */
    public static function exceptionProvider(): iterable
    {
        yield 'authentication' => [401, AuthenticationException::class];
        yield 'validation' => [422, ValidationException::class];
        yield 'rate limit' => [429, RateLimitException::class];
    }

    #[DataProvider('exceptionProvider')]
    public function testMapsApiErrors(int $status, string $exception): void
    {
        $http = new MockHttpClient(new Response(
            $status,
            [],
            '{"error":{"message":"Failed","code":"bad_request"}}',
        ));
        $client = $this->client($http, retry: 0);

        try {
            $client->applications()->me();
            self::fail('Expected exception was not thrown.');
        } catch (AuthenticationException|ValidationException|RateLimitException $error) {
            self::assertInstanceOf($exception, $error);
            self::assertSame('Failed', $error->getMessage());
            self::assertSame('bad_request', $error->error->code);
        }
    }

    private function client(MockHttpClient $http, int $retry = 2): Client
    {
        $factory = new HttpFactory();

        return new Client(
            new Config('https://ai.justin.my', new ApiKey('ai_live_test'), 10, 'test-suite', $retry),
            $http,
            $factory,
            $factory,
        );
    }
}
