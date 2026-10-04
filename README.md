# emeq/mistral-api

Laravel-SDK voor de Mistral API op het EU-endpoint `https://api.eu.mistral.ai`. Saloon v4, alleen HTTP, auth en DTO's: OCR (`POST /v1/ocr`) en chat completions met een strict JSON-schema (`POST /v1/chat/completions`).

De base-URL ligt vast. De Files API wordt nooit gebruikt: die bestaat niet op regionale endpoints, dus documenten gaan altijd inline als base64-data-URL.

## Aansluiten

De host levert de API-sleutel via `MistralCredentialResolver`:

```php
use Emeq\MistralApi\Contracts\MistralCredentialResolver;

$this->app->bind(MistralCredentialResolver::class, HubMistralCredentialResolver::class);
```

## Gebruik

```php
use Emeq\MistralApi\Data\ChatMessage;
use Emeq\MistralApi\Data\Document;
use Emeq\MistralApi\Mistral;

$mistral = app(Mistral::class);

$ocr = $mistral->ocr(Document::fromBytes($bytes, 'application/pdf'), 'mistral-ocr-latest');
$ocr->pages[0]->markdown;
$ocr->pagesProcessed;

$completion = $mistral->chat(
    model: 'ministral-14b-latest',
    messages: [ChatMessage::user('Lees de tabel', Document::fromBytes($jpeg, 'image/jpeg'))],
    schemaName: 'weekstaat',
    schema: $schema,
    temperature: 0.0,
);
$completion->content;
$completion->totalTokens;
```

`Document` kiest `image_url` voor `image/*` en `document_url` voor al het andere.

## Fouten

Elke foutstatus wordt een `MistralException` met een `ErrorKind` (401 `Authentication`, 422 `Validation`, 429 `RateLimited`, 5xx `ServerError`, enz.). De API-sleutel staat nooit in de message. De SDK probeert niet opnieuw: elke OCR- of chat-aanroep kost geld, dus de host bepaalt het retrybeleid.

## Testen zonder netwerk

```php
use Emeq\MistralApi\Testing\FakeMistral;

$this->app->instance(Mistral::class, $fake = new FakeMistral(ocr: $ocrResult, chat: $completion));
$fake->ocrCalls;
$fake->chatCalls;
```

`ocr:` en `chat:` accepteren ook een `MistralException`, die de fake dan werpt.

## Partner-documentatie

`docs/partners/mistral/` bevat de gebruikte uittreksels uit de officiële docs, met bron en datum.

## Ontwikkelen

```bash
composer test
composer analyse
composer format
```
