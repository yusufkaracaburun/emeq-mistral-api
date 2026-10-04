<?php

declare(strict_types=1);

use Emeq\MistralApi\Data\Document;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

it('stuurt OCR naar het EU-endpoint met de bearer-sleutel', function (): void {
    $mock = MockClient::global([MockResponse::make(ocrResponse())]);

    mistral()->ocr(Document::fromBytes('%PDF-fake', 'application/pdf'), 'mistral-ocr-test');

    $sent = sentRequests($mock)[0];

    expect($sent->getUrl())->toBe('https://api.eu.mistral.ai/v1/ocr')
        ->and($sent->headers()->get('Authorization'))->toBe('Bearer '.TEST_API_KEY);
});

it('stuurt een PDF inline als document_url-data-URL', function (): void {
    $mock = MockClient::global([MockResponse::make(ocrResponse())]);

    mistral()->ocr(Document::fromBytes('%PDF-fake', 'application/pdf'), 'mistral-ocr-test');

    expect(sentBody($mock))->toBe([
        'model' => 'mistral-ocr-test',
        'document' => [
            'type' => 'document_url',
            'document_url' => 'data:application/pdf;base64,'.base64_encode('%PDF-fake'),
        ],
    ]);
});

it('stuurt een afbeelding inline als image_url-data-URL', function (): void {
    $mock = MockClient::global([MockResponse::make(ocrResponse())]);

    mistral()->ocr(Document::fromBytes('fake-jpeg-bytes', 'image/jpeg'), 'mistral-ocr-test');

    expect(sentBody($mock)['document'])->toBe([
        'type' => 'image_url',
        'image_url' => 'data:image/jpeg;base64,'.base64_encode('fake-jpeg-bytes'),
    ]);
});

it('gebruikt nooit de Files API', function (string $mimeType): void {
    $mock = MockClient::global([MockResponse::make(ocrResponse())]);

    mistral()->ocr(Document::fromBytes('fake', $mimeType), 'mistral-ocr-test');

    $document = sentBody($mock)['document'];

    expect($document['type'])->not->toBe('file')
        ->and($document)->not->toHaveKey('file_id')
        ->and(sentRequests($mock))->toHaveCount(1)
        ->and(sentRequests($mock)[0]->getUrl())->not->toContain('/v1/files');
})->with(['application/pdf', 'image/png']);

it('geeft per pagina de markdown en het paginaverbruik terug', function (): void {
    MockClient::global([MockResponse::make(ocrResponse())]);

    $result = mistral()->ocr(Document::fromBytes('fake', 'application/pdf'), 'mistral-ocr-test');

    expect($result->model)->toBe('mistral-ocr-test')
        ->and($result->pagesProcessed)->toBe(2)
        ->and($result->pages)->toHaveCount(2)
        ->and($result->pages[0]->index)->toBe(0)
        ->and($result->pages[0]->markdown)->toStartWith('| Naam | ma | di |')
        ->and($result->pages[1]->index)->toBe(1)
        ->and($result->pages[1]->markdown)->toBe('# Pagina twee');
});

it('stuurt geen confidence_scores_granularity mee', function (): void {
    $mock = MockClient::global([MockResponse::make(ocrResponse())]);

    mistral()->ocr(Document::fromBytes('fake', 'image/png'), 'mistral-ocr-test');

    expect(sentBody($mock))->toHaveKeys(['model', 'document'])->toHaveCount(2);
});
