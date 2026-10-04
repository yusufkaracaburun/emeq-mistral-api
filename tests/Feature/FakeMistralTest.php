<?php

declare(strict_types=1);

use Emeq\MistralApi\Data\ChatCompletion;
use Emeq\MistralApi\Data\ChatMessage;
use Emeq\MistralApi\Data\Document;
use Emeq\MistralApi\Data\OcrResult;
use Emeq\MistralApi\Enums\ErrorKind;
use Emeq\MistralApi\Exceptions\MistralException;
use Emeq\MistralApi\Mistral;
use Emeq\MistralApi\Testing\FakeMistral;
use Saloon\Http\Faking\MockClient;

it('doet geen enkel verzoek en levert standaard bruikbare antwoorden', function (): void {
    $mock = MockClient::global([]);
    $fake = new FakeMistral;

    $ocr = $fake->ocr(Document::fromBytes('fake', 'application/pdf'), 'mistral-ocr-test');
    $chat = $fake->chat('fake-chat-model', [ChatMessage::user('x')], 'naam', ['type' => 'object']);

    expect($ocr->pages)->not->toBeEmpty()
        ->and($ocr->pagesProcessed)->toBe(count($ocr->pages))
        ->and(json_decode($chat->content, true))->toBeArray();

    $mock->assertNothingSent();
});

it('geeft de ingestelde antwoorden terug en onthoudt de aanroepen', function (): void {
    $ocr = OcrResult::fromArray(ocrResponse());
    $chat = ChatCompletion::fromArray(chatResponse('{"ok":true}'));
    $fake = new FakeMistral(ocr: $ocr, chat: $chat);

    $fake->ocr(Document::fromBytes('fake', 'image/png'), 'mistral-ocr-test');

    expect($fake->ocr(Document::fromBytes('fake', 'image/png'), 'mistral-ocr-test'))->toBe($ocr)
        ->and($fake->chat('fake-chat-model', [ChatMessage::user('x')], 'naam', ['type' => 'object']))->toBe($chat)
        ->and($fake->ocrCalls)->toHaveCount(2)
        ->and($fake->ocrCalls[0]['model'])->toBe('mistral-ocr-test')
        ->and($fake->chatCalls)->toHaveCount(1)
        ->and($fake->chatCalls[0]['schema'])->toBe(['type' => 'object']);
});

it('werpt een ingestelde exceptie', function (): void {
    $fake = new FakeMistral(chat: new MistralException('Fake limiet', ErrorKind::RateLimited, 429));

    expect(fn () => $fake->chat('fake-chat-model', [ChatMessage::user('x')], 'naam', ['type' => 'object']))
        ->toThrow(MistralException::class, 'Fake limiet');
});

it('is te binden op de plek van de echte client', function (): void {
    app()->instance(Mistral::class, new FakeMistral);

    expect(app(Mistral::class))->toBeInstanceOf(FakeMistral::class);
});
