<?php

declare(strict_types=1);

namespace Emeq\MistralApi\Enums;

enum ErrorKind: string
{
    case BadRequest = 'bad_request';
    case Authentication = 'authentication';
    case Forbidden = 'forbidden';
    case NotFound = 'not_found';
    case Validation = 'validation';
    case RateLimited = 'rate_limited';
    case ServerError = 'server_error';
    case UnexpectedResponse = 'unexpected_response';
    case Unknown = 'unknown';

    public static function fromStatus(int $status): self
    {
        return match (true) {
            $status === 400 => self::BadRequest,
            $status === 401 => self::Authentication,
            $status === 403 => self::Forbidden,
            $status === 404 => self::NotFound,
            $status === 422 => self::Validation,
            $status === 429 => self::RateLimited,
            $status >= 500 => self::ServerError,
            default => self::Unknown,
        };
    }
}
