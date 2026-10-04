<?php

declare(strict_types=1);

namespace Emeq\MistralApi\Data;

use Emeq\MistralApi\Enums\ErrorKind;
use Emeq\MistralApi\Exceptions\MistralException;

final readonly class ChatCompletion
{
    public function __construct(
        public string $model,
        public string $content,
        public int $promptTokens,
        public int $completionTokens,
        public int $totalTokens,
    ) {}

    /**
     * @param  array<array-key, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $content = $data['choices'][0]['message']['content'] ?? null;

        if (! is_string($content)) {
            throw new MistralException(
                message: 'Mistral gaf een chat-antwoord zonder tekst-content.',
                kind: ErrorKind::UnexpectedResponse,
                status: 200,
            );
        }

        $usage = is_array($data['usage'] ?? null) ? $data['usage'] : [];

        return new self(
            model: (string) ($data['model'] ?? ''),
            content: $content,
            promptTokens: (int) ($usage['prompt_tokens'] ?? 0),
            completionTokens: (int) ($usage['completion_tokens'] ?? 0),
            totalTokens: (int) ($usage['total_tokens'] ?? 0),
        );
    }
}
