<?php

declare(strict_types=1);

use Emeq\MistralApi\Data\ChatMessage;
use Emeq\MistralApi\Data\Document;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

/**
 * @return array<string, mixed>
 */
function weekstaatSchema(): array
{
    return [
        'type' => 'object',
        'additionalProperties' => false,
        'required' => ['rows'],
        'properties' => [
            'rows' => [
                'type' => 'array',
                'items' => [
                    'type' => 'object',
                    'properties' => [
                        'naam' => ['type' => ['string', 'null']],
                        'ma' => ['type' => ['number', 'null']],
                    ],
                ],
            ],
        ],
    ];
}

it('stuurt chat naar het EU-endpoint met de bearer-sleutel', function (): void {
    $mock = MockClient::global([MockResponse::make(chatResponse())]);

    mistral()->chat('fake-chat-model', [ChatMessage::user('Lees dit')], 'weekstaat', weekstaatSchema());

    $sent = sentRequests($mock)[0];

    expect($sent->getUrl())->toBe('https://api.eu.mistral.ai/v1/chat/completions')
        ->and($sent->headers()->get('Authorization'))->toBe('Bearer '.TEST_API_KEY);
});

it('stuurt response_format als strict json_schema met het schema ongewijzigd', function (): void {
    $mock = MockClient::global([MockResponse::make(chatResponse())]);

    mistral()->chat('fake-chat-model', [ChatMessage::user('Lees dit')], 'weekstaat', weekstaatSchema());

    expect(sentBody($mock)['response_format'])->toBe([
        'type' => 'json_schema',
        'json_schema' => [
            'name' => 'weekstaat',
            'schema' => weekstaatSchema(),
            'strict' => true,
        ],
    ]);
});

it('stuurt tekst en base64-afbeeldingen als content-delen', function (): void {
    $mock = MockClient::global([MockResponse::make(chatResponse())]);

    mistral()->chat(
        'fake-chat-model',
        [ChatMessage::user('Lees de tabel', Document::fromBytes('fake-jpeg', 'image/jpeg'))],
        'weekstaat',
        weekstaatSchema(),
        temperature: 0.0,
    );

    $body = sentBody($mock);

    expect($body['model'])->toBe('fake-chat-model')
        ->and($body['temperature'])->toBe(0.0)
        ->and($body['messages'])->toBe([
            [
                'role' => 'user',
                'content' => [
                    ['type' => 'text', 'text' => 'Lees de tabel'],
                    ['type' => 'image_url', 'image_url' => 'data:image/jpeg;base64,'.base64_encode('fake-jpeg')],
                ],
            ],
        ]);
});

it('stuurt een bericht zonder afbeelding als platte tekst en laat temperature weg als die niet gekozen is', function (): void {
    $mock = MockClient::global([MockResponse::make(chatResponse())]);

    mistral()->chat('fake-chat-model', [ChatMessage::user('Alleen tekst')], 'weekstaat', weekstaatSchema());

    expect(sentBody($mock)['messages'])->toBe([['role' => 'user', 'content' => 'Alleen tekst']])
        ->and(sentBody($mock))->not->toHaveKey('temperature');
});

it('geeft de content-string en het tokenverbruik terug', function (): void {
    MockClient::global([MockResponse::make(chatResponse())]);

    $completion = mistral()->chat('fake-chat-model', [ChatMessage::user('Lees dit')], 'weekstaat', weekstaatSchema());

    expect($completion->content)->toBe('{"rows":[{"naam":"Test","ma":8}]}')
        ->and($completion->model)->toBe('fake-chat-model')
        ->and($completion->promptTokens)->toBe(120)
        ->and($completion->completionTokens)->toBe(30)
        ->and($completion->totalTokens)->toBe(150);
});

it('stuurt een object-schema ongewijzigd op de wire, lege objecten blijven {}', function (): void {
    $mock = MockClient::global([MockResponse::make(chatResponse())]);
    $schema = json_decode('{"type":"object","properties":{}}');

    mistral()->chat('fake-chat-model', [ChatMessage::user('x')], 'leeg', $schema);

    $wire = (string) sentRequests($mock)[0]->body();

    expect($wire)->toContain('"schema":{"type":"object","properties":{}}')
        ->and($wire)->not->toContain('"properties":[]');
});
