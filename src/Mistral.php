<?php

declare(strict_types=1);

namespace Emeq\MistralApi;

use Emeq\MistralApi\Contracts\MistralCredentialResolver;
use Emeq\MistralApi\Data\ChatCompletion;
use Emeq\MistralApi\Data\ChatMessage;
use Emeq\MistralApi\Data\Document;
use Emeq\MistralApi\Data\OcrResult;
use Emeq\MistralApi\Http\MistralConnector;
use Emeq\MistralApi\Http\Request\ChatCompletionRequest;
use Emeq\MistralApi\Http\Request\OcrRequest;
use Emeq\MistralApi\Support\ErrorMapper;
use Saloon\Http\Request;

class Mistral
{
    private ?MistralConnector $connector = null;

    private ?string $apiKey = null;

    public function __construct(
        private readonly MistralCredentialResolver $resolver,
    ) {}

    public function ocr(Document $document, string $model): OcrResult
    {
        return OcrResult::fromArray($this->send(new OcrRequest($document, $model)));
    }

    /**
     * @param  list<ChatMessage>  $messages
     * @param  array<string, mixed>|object  $schema
     */
    public function chat(string $model, array $messages, string $schemaName, array|object $schema, ?float $temperature = null): ChatCompletion
    {
        return ChatCompletion::fromArray($this->send(new ChatCompletionRequest($model, $messages, $schemaName, $schema, $temperature)));
    }

    /**
     * @return array<array-key, mixed>
     */
    private function send(Request $request): array
    {
        $response = $this->connector()->send($request);

        if ($response->failed()) {
            throw ErrorMapper::fromResponse($response, $this->apiKey());
        }

        $data = $response->json();

        return is_array($data) ? $data : [];
    }

    private function connector(): MistralConnector
    {
        return $this->connector ??= new MistralConnector($this->apiKey());
    }

    private function apiKey(): string
    {
        return $this->apiKey ??= $this->resolver->resolve();
    }
}
