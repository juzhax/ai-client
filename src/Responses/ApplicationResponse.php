<?php

declare(strict_types=1);

namespace Juzhax\AiClient\Responses;

use Juzhax\AiClient\Data\JsonData;

final readonly class ApplicationResponse
{
    public function __construct(public string $id, public ?string $name, public JsonData $data)
    {
    }
}
