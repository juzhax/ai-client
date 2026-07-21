<?php

declare(strict_types=1);

namespace Juzhax\AiClient\Support;

use Juzhax\AiClient\Data\JsonData;
use Juzhax\AiClient\Responses\AgentResponse;
use Juzhax\AiClient\Responses\ApplicationResponse;
use Juzhax\AiClient\Responses\HealthResponse;
use Juzhax\AiClient\Responses\PromptResponse;
use Juzhax\AiClient\Responses\PromptRunResponse;
use Juzhax\AiClient\Responses\RequestResponse;
use Juzhax\AiClient\Responses\WorkflowRunResponse;
use stdClass;

final class ResponseMapper
{
    public function health(stdClass $data): HealthResponse
    {
        return new HealthResponse($this->string($data, 'status', 'unknown'), new JsonData($data));
    }

    public function application(stdClass $data): ApplicationResponse
    {
        return new ApplicationResponse(
            $this->string($data, 'id'),
            $this->nullableString($data, 'name'),
            new JsonData($data),
        );
    }

    public function agent(stdClass $data): AgentResponse
    {
        return new AgentResponse(
            $this->string($data, 'id'),
            $this->nullableString($data, 'output'),
            new JsonData($data),
        );
    }

    public function prompt(stdClass $data): PromptResponse
    {
        return new PromptResponse($this->string($data, 'content'), new JsonData($data));
    }

    public function promptRun(stdClass $data): PromptRunResponse
    {
        return new PromptRunResponse(
            $this->string($data, 'request_uuid'),
            $this->string($data, 'output'),
            isset($data->structured) && $data->structured instanceof stdClass
                ? new JsonData($data->structured)
                : null,
            $this->string($data, 'provider'),
            $this->string($data, 'model'),
            new JsonData(isset($data->usage) && $data->usage instanceof stdClass ? $data->usage : new stdClass()),
            new JsonData(isset($data->cost) && $data->cost instanceof stdClass ? $data->cost : new stdClass()),
            $this->nullableString($data, 'finish_reason'),
            isset($data->duration_ms) && is_numeric($data->duration_ms) ? (int) $data->duration_ms : null,
            new JsonData($data),
        );
    }

    public function workflow(stdClass $data): WorkflowRunResponse
    {
        return new WorkflowRunResponse(
            $this->string($data, 'id'),
            $this->string($data, 'status', 'unknown'),
            new JsonData($data),
        );
    }

    public function request(stdClass $data): RequestResponse
    {
        return new RequestResponse(
            $this->string($data, 'id'),
            $this->string($data, 'status', 'unknown'),
            new JsonData($data),
        );
    }

    private function string(stdClass $data, string $property, string $default = ''): string
    {
        $value = $data->{$property} ?? $default;

        return is_string($value) || is_numeric($value) ? (string) $value : $default;
    }

    private function nullableString(stdClass $data, string $property): ?string
    {
        if (!isset($data->{$property})) {
            return null;
        }

        return is_string($data->{$property}) || is_numeric($data->{$property})
            ? (string) $data->{$property}
            : null;
    }
}
