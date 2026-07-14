<?php

declare(strict_types=1);

namespace Juzhax\AiClient\Contracts;

interface RequestData
{
    /** @return array<string, mixed> */
    public function toArray(): array;
}
