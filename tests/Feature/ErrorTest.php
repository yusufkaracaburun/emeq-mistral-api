<?php

declare(strict_types=1);

use Emeq\MistralApi\Data\ChatMessage;
use Emeq\MistralApi\Data\Document;
use Emeq\MistralApi\Enums\ErrorKind;
use Emeq\MistralApi\Exceptions\MistralException;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

function mistralError(int $status, string $type, string $message = 'Fake foutmelding'): MockResponse
{
    return MockResponse::make([
        'object' => 'error',
        'message' => $message,
        'type' => $type,
        'param' => null,
        'code' => 'fake_code',
    ], $status);
}

function catchMistral(callable $call): MistralException
{
    try {
        $call();
    } catch (MistralException $e) {
        return $e;
    }

    throw new RuntimeException('verwachtte een MistralException');
}

it('maakt van een foutstatus een getypeerde exceptie', function (int $status, ErrorKind $kind): void {
    MockClient::global([mistralError($status, 'fake_error')]);

    $e = catchMistral(fn () => mistral()->ocr(Document::fromBytes('fake', 'image/png'), 'mistral-ocr-test'));

    expect($e->kind)->toBe($kind)
        ->and($e->status)->toBe($status)
        ->and($e->getMessage())->toBe('Fake foutmelding');
})->with([
    [400, ErrorKind::BadRequest],
    [401, ErrorKind::Authentication],
    [403, ErrorKind::Forbidden],
    [404, ErrorKind::NotFound],
    [422, ErrorKind::Validation],
    [429, ErrorKind::RateLimited],
    [500, ErrorKind::ServerError],
    [502, ErrorKind::ServerError],
    [503, ErrorKind::ServerError],
    [504, ErrorKind::ServerError],
    [418, ErrorKind::Unknown],
]);

it('werpt dezelfde getypeerde exceptie voor chat', function (): void {
    MockClient::global([mistralError(429, 'rate_limit_error')]);

    $e = catchMistral(fn () => mistral()->chat('fake-chat-model', [ChatMessage::user('x')], 'naam', ['type' => 'object']));

    expect($e->kind)->toBe(ErrorKind::RateLimited);
});

it('valt terug op een eigen melding bij een foutbody zonder string-message', function (): void {
    MockClient::global([MockResponse::make(['detail' => [['loc' => ['body', 'model'], 'msg' => 'Field required', 'type' => 'missing']]], 422)]);

    $e = catchMistral(fn () => mistral()->ocr(Document::fromBytes('fake', 'image/png'), 'mistral-ocr-test'));

    expect($e->kind)->toBe(ErrorKind::Validation)
        ->and($e->getMessage())->toBe('Mistral gaf HTTP 422');
});

it('zet de API-sleutel nooit in een exception-message', function (int $status): void {
    MockClient::global([mistralError($status, 'authentication_error', 'Invalid key '.TEST_API_KEY.' for workspace')]);

    $e = catchMistral(fn () => mistral()->ocr(Document::fromBytes('fake', 'image/png'), 'mistral-ocr-test'));

    expect($e->getMessage())->not->toContain(TEST_API_KEY)
        ->and((string) $e)->not->toContain(TEST_API_KEY);
})->with([401, 422, 429, 500]);

it('weigert een chat-antwoord zonder string-content', function (mixed $content): void {
    MockClient::global([MockResponse::make(chatResponse($content))]);

    $e = catchMistral(fn () => mistral()->chat('fake-chat-model', [ChatMessage::user('x')], 'naam', ['type' => 'object']));

    expect($e->kind)->toBe(ErrorKind::UnexpectedResponse);
})->with([null, [['type' => 'text', 'text' => 'x']]]);
