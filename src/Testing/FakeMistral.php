<?php

declare(strict_types=1);

namespace Emeq\MistralApi\Testing;

use Emeq\MistralApi\Data\ChatCompletion;
use Emeq\MistralApi\Data\ChatMessage;
use Emeq\MistralApi\Data\Document;
use Emeq\MistralApi\Data\OcrPage;
use Emeq\MistralApi\Data\OcrResult;
use Emeq\MistralApi\Exceptions\MistralException;
use Emeq\MistralApi\Mistral;

final class FakeMistral extends Mistral
{
    /** @var list<array{document: Document, model: string}> */
    public array $ocrCalls = [];

    /** @var list<array{model: string, messages: list<ChatMessage>, schema_name: string, schema: array<string, mixed>|object, temperature: ?float}> */
    public array $chatCalls = [];

    private OcrResult|MistralException $ocr;

    private ChatCompletion|MistralException $chat;

    public function __construct(
        OcrResult|MistralException|null $ocr = null,
        ChatCompletion|MistralException|null $chat = null,
    ) {
        $this->ocr = $ocr ?? new OcrResult(
            model: 'fake-ocr-model',
            pages: [new OcrPage(0, "| Naam | ma | di |\n| --- | --- | --- |\n| Fake | 8 | 8 |")],
            pagesProcessed: 1,
        );

        $this->chat = $chat ?? new ChatCompletion(
            model: 'fake-chat-model',
            content: '{}',
            promptTokens: 0,
            completionTokens: 0,
            totalTokens: 0,
        );
    }

    public function ocr(Document $document, string $model): OcrResult
    {
        $this->ocrCalls[] = ['document' => $document, 'model' => $model];

        return $this->ocr instanceof MistralException ? throw $this->ocr : $this->ocr;
    }

    public function chat(string $model, array $messages, string $schemaName, array|object $schema, ?float $temperature = null): ChatCompletion
    {
        $this->chatCalls[] = [
            'model' => $model,
            'messages' => $messages,
            'schema_name' => $schemaName,
            'schema' => $schema,
            'temperature' => $temperature,
        ];

        return $this->chat instanceof MistralException ? throw $this->chat : $this->chat;
    }
}
