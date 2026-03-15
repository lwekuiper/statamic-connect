<?php

namespace Lwekuiper\StatamicConnect\Exceptions;

use Exception;

class IntegrationException extends Exception
{
    public static function apiError(string $integration, string $message): static
    {
        return new static("[Connect:{$integration}] API error: {$message}");
    }

    public static function configMissing(string $integration): static
    {
        return new static("[Connect:{$integration}] Required configuration is missing.");
    }
}
