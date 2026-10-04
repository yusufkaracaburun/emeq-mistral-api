<?php

declare(strict_types=1);

use Emeq\MistralApi\Mistral;
use Emeq\MistralApi\Tests\Support\StaticCredentialResolver;
use Emeq\MistralApi\Tests\TestCase;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\PendingRequest;
use Saloon\Http\Response;

uses(TestCase::class)->in(__DIR__);

uses()->afterEach(function (): void {
    MockClient::destroyGlobal();
})->in(__DIR__);

const TEST_API_KEY = 'test-key-0123456789abcdef';

function mistral(): Mistral
{
    return new Mistral(new StaticCredentialResolver(TEST_API_KEY));
}

/**
 * @return list<PendingRequest>
 */
function sentRequests(MockClient $mock): array
{
    return array_map(
        static fn (Response $response): PendingRequest => $response->getPendingRequest(),
        $mock->getRecordedResponses(),
    );
}

/**
 * @return array<string, mixed>
 */
function sentBody(MockClient $mock, int $index = 0): array
{
    return sentRequests($mock)[$index]->body()?->all() ?? [];
}

/**
 * @return array<string, mixed>
 */
function ocrResponse(): array
{
    return [
        'pages' => [
            [
                'index' => 0,
                'markdown' => "| Naam | ma | di |\n| --- | --- | --- |\n| Test | 8 | 7,5 |",
                'images' => [],
                'dimensions' => ['dpi' => 200, 'height' => 2200, 'width' => 1700],
                'confidence_scores' => [
                    'average_page_confidence_score' => 0.91,
                    'minimum_page_confidence_score' => 0.42,
                    'word_confidence_scores' => [
                        ['text' => 'Naam', 'confidence' => 0.99, 'start_index' => 2],
                        ['text' => '7,5', 'confidence' => 0.42, 'start_index' => 45],
                    ],
                ],
            ],
            [
                'index' => 1,
                'markdown' => '# Pagina twee',
                'images' => [],
                'dimensions' => null,
                'confidence_scores' => null,
            ],
        ],
        'model' => 'mistral-ocr-test',
        'document_annotation' => null,
        'usage_info' => ['pages_processed' => 2, 'doc_size_bytes' => 1234],
    ];
}

/**
 * @return array<string, mixed>
 */
function chatResponse(mixed $content = '{"rows":[{"naam":"Test","ma":8}]}'): array
{
    return [
        'id' => 'fake-completion-id',
        'object' => 'chat.completion',
        'created' => 1700000000,
        'model' => 'fake-chat-model',
        'choices' => [
            [
                'index' => 0,
                'finish_reason' => 'stop',
                'message' => ['role' => 'assistant', 'tool_calls' => null, 'content' => $content],
            ],
        ],
        'usage' => ['prompt_tokens' => 120, 'completion_tokens' => 30, 'total_tokens' => 150],
    ];
}
