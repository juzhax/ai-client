<?php

declare(strict_types=1);

namespace Juzhax\AiClient\Exception;

use Juzhax\AiClient\Responses\ApiErrorResponse;

class ApiException extends AiException
{
    public function __construct(public readonly ApiErrorResponse $error)
    {
        parent::__construct($error->message, $error->status);
    }
}
