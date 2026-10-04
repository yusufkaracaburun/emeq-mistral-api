<?php

declare(strict_types=1);

use Emeq\MistralApi\Contracts\MistralCredentialResolver;
use Emeq\MistralApi\Data\Document;
use Emeq\MistralApi\Mistral;
use Emeq\MistralApi\Tests\Support\StaticCredentialResolver;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

it('bouwt de client met de sleutel uit de door de host gebonden resolver', function (): void {
    app()->instance(MistralCredentialResolver::class, new StaticCredentialResolver('host-key'));
    $mock = MockClient::global([MockResponse::make(ocrResponse())]);

    app(Mistral::class)->ocr(Document::fromBytes('fake', 'image/png'), 'mistral-ocr-test');

    expect(sentRequests($mock)[0]->headers()->get('Authorization'))->toBe('Bearer host-key');
});
