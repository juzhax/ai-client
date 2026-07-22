<?php

declare(strict_types=1);

namespace Juzhax\AiClient;

use InvalidArgumentException;

final readonly class Config
{
    public function __construct(
        public string $baseUrl,
        public ApiKey $apiKey,
        public float $timeout = 30.0,
        public string $userAgent = 'juzhax-ai-client/1.1.2',
        public int $retry = 2,
    ) {
        if (!filter_var($baseUrl, FILTER_VALIDATE_URL)) {
            throw new InvalidArgumentException('The base URL must be a valid URL.');
        }
        if ($timeout <= 0) {
            throw new InvalidArgumentException('The timeout must be greater than zero.');
        }
        if ($retry < 0) {
            throw new InvalidArgumentException('The retry count cannot be negative.');
        }
        if (trim($userAgent) === '') {
            throw new InvalidArgumentException('The user agent cannot be empty.');
        }
    }
}
