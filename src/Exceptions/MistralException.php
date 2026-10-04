<?php

declare(strict_types=1);

namespace Emeq\MistralApi\Exceptions;

use Emeq\MistralApi\Enums\ErrorKind;
use RuntimeException;

class MistralException extends RuntimeException
{
    public function __construct(
        string $message,
        public readonly ErrorKind $kind,
        public readonly int $status,
    ) {
        parent::__construct($message, $status);
    }
}
