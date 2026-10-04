<?php

declare(strict_types=1);

namespace Emeq\MistralApi\Http\Request;

use Emeq\MistralApi\Data\ChatMessage;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

class ChatCompletionRequest extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    /**
     * @param  list<ChatMessage>  $messages
     * @param  array<string, mixed>|object  $schema
     */
    public function __construct(
        private readonly string $model,
        private readonly array $messages,
        private readonly string $schemaName,
        private readonly array|object $schema,
        private readonly ?float $temperature = null,
    ) {}

    public function resolveEndpoint(): string
    {
        return '/v1/chat/completions';
    }

    /**
     * @return array<string, mixed>
     */
    protected function defaultBody(): array
    {
        return array_filter([
            'model' => $this->model,
            'temperature' => $this->temperature,
            'messages' => array_map(static fn (ChatMessage $message): array => $message->toArray(), $this->messages),
            'response_format' => [
                'type' => 'json_schema',
                'json_schema' => [
                    'name' => $this->schemaName,
                    'schema' => $this->schema,
                    'strict' => true,
                ],
            ],
        ], static fn (mixed $value): bool => $value !== null);
    }
}
