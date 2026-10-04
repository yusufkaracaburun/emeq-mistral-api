<?php

declare(strict_types=1);

namespace Emeq\MistralApi\Support;

use Emeq\MistralApi\Enums\ErrorKind;
use Emeq\MistralApi\Exceptions\MistralException;
use Saloon\Http\Response;
use Throwable;

final class ErrorMapper
{
    public static function fromResponse(Response $response, #[\SensitiveParameter] string $apiKey): MistralException
    {
        $status = $response->status();

        try {
            $decoded = $response->json();
            $body = is_array($decoded) ? $decoded : [];
        } catch (Throwable) {
            $body = [];
        }

        $message = is_string($body['message'] ?? null) && $body['message'] !== ''
            ? str_replace($apiKey, '[redacted]', $body['message'])
            : 'Mistral gaf HTTP '.$status;

        return new MistralException(
            message: $message,
            kind: ErrorKind::fromStatus($status),
            status: $status,
        );
    }
}
