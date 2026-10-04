<?php

declare(strict_types=1);

namespace Emeq\MistralApi\Http\Request;

use Emeq\MistralApi\Data\Document;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

class OcrRequest extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    public function __construct(
        private readonly Document $document,
        private readonly string $model,
    ) {}

    public function resolveEndpoint(): string
    {
        return '/v1/ocr';
    }

    /**
     * @return array<string, mixed>
     */
    protected function defaultBody(): array
    {
        return [
            'model' => $this->model,
            'document' => $this->document->toChunk(),
        ];
    }
}
