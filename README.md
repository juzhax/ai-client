# Juzhax AI Client

A framework-independent PHP 8.3+ SDK for the [Justin AI Platform](https://ai.justin.my) REST API. It talks only to the AI Platform and never directly to an underlying AI provider.

## Installation

```bash
composer require juzhax/ai-client
```

Install any PSR-18 client and PSR-17 implementation. Guzzle is one option:

```bash
composer require guzzlehttp/guzzle
```

## Configuration

```php
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Psr7\HttpFactory;
use Juzhax\AiClient\ApiKey;
use Juzhax\AiClient\Client;
use Juzhax\AiClient\Config;

$factory = new HttpFactory();
$config = new Config(
    baseUrl: 'https://ai.justin.my',
    apiKey: new ApiKey($_ENV['AI_PLATFORM_KEY']),
    timeout: 30,
    userAgent: 'my-app/1.0',
    retry: 2,
);

$client = new Client(
    config: $config,
    httpClient: new GuzzleClient(['timeout' => $config->timeout]),
    requestFactory: $factory,
    streamFactory: $factory,
);
```

PSR-18 does not define a portable per-request timeout option. Set the configured `timeout` on your chosen PSR-18 implementation, as shown above. The SDK remains independent of that implementation.

Every request sends bearer authentication plus `Accept: application/json`, `Content-Type: application/json`, and the configured `User-Agent`. Retries apply only to 429, 502, 503, and 504 responses. `retry` is the number of retries after the first attempt.

## Usage

```php
$health = $client->health()->ping();
$application = $client->applications()->me();
$request = $client->requests()->find('req_123');
```

All public results are immutable DTOs. Provider-defined JSON is available through a `JsonData` object rather than an associative response array.

### Workflows

```php
use Juzhax\AiClient\Data\JsonData;
use Juzhax\AiClient\Requests\WorkflowRunRequest;

$input = (object) ['customer_id' => 'cus_123', 'question' => 'Where is my order?'];
$run = $client->workflows()->run('customer-support', new WorkflowRunRequest(new JsonData($input)));
$latest = $client->workflows()->status($run->id);
```

### Agents

```php
use Juzhax\AiClient\Requests\AgentRunRequest;

$result = $client->agents()->run('support', new AgentRunRequest('Help me reset my password'));
echo $result->output;
```

### Prompts

```php
use Juzhax\AiClient\Data\JsonData;
use Juzhax\AiClient\Requests\PromptRunRequest;

$variables = new JsonData((object) ['topic' => 'Laravel queues']);
$result = $client->prompts()->run(
    'explain-topic',
    new PromptRunRequest(variables: $variables),
);

echo $result->output;
echo $result->usage->value->total_tokens;
```

The API key must be project-scoped with the `prompts:run` ability. The prompt must have an active
version and reference a registered agent. By default, the platform uses the provider and model configured
for the prompt. Pass `provider` and `model` to `PromptRunRequest` only when you need to override them;
override values must match active catalogue entries.

## Framework examples

Laravel does not need a package integration or facade. Bind the configured client in a service provider:

```php
$this->app->singleton(Client::class, fn () => new Client(
    config: new Config(config('services.ai.url'), new ApiKey(config('services.ai.key'))),
    httpClient: new \GuzzleHttp\Client(['timeout' => 30]),
    requestFactory: new \GuzzleHttp\Psr7\HttpFactory(),
    streamFactory: new \GuzzleHttp\Psr7\HttpFactory(),
));
```

In WordPress, load Composer's autoloader and construct the same plain PHP client:

```php
require_once __DIR__.'/vendor/autoload.php';

$factory = new \GuzzleHttp\Psr7\HttpFactory();
$client = new Client(
    new Config('https://ai.justin.my', new ApiKey(get_option('ai_platform_key'))),
    new \GuzzleHttp\Client(['timeout' => 30]),
    $factory,
    $factory,
);
```

Symfony's `Psr18Client` can be supplied with Symfony's PSR-17 factories in exactly the same constructor; no SDK adapter is required.

## Error handling

```php
use Juzhax\AiClient\Exception\AuthenticationException;
use Juzhax\AiClient\Exception\NetworkException;
use Juzhax\AiClient\Exception\RateLimitException;
use Juzhax\AiClient\Exception\ValidationException;

try {
    $result = $client->agents()->run('support', new AgentRunRequest('Hello'));
} catch (AuthenticationException $e) {
    // Invalid or unauthorized API key.
} catch (ValidationException $e) {
    // Inspect $e->error, an ApiErrorResponse DTO.
} catch (RateLimitException $e) {
    // Retries were exhausted.
} catch (NetworkException $e) {
    // Transport exceptions are wrapped and never exposed directly.
}
```

Other non-success API responses throw `ApiException`; malformed responses throw `AiException`.

## Development

```bash
composer install
composer test
```

Tests use a mock PSR-18 client and never make network requests.
