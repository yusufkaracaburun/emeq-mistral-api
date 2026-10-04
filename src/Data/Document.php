<?php

declare(strict_types=1);

namespace Emeq\MistralApi\Data;

final readonly class Document
{
    private function __construct(
        private string $mimeType,
        private string $base64,
    ) {}

    public static function fromBytes(string $bytes, string $mimeType): self
    {
        return new self($mimeType, base64_encode($bytes));
    }

    /**
     * @return array{type: 'image_url', image_url: string}|array{type: 'document_url', document_url: string}
     */
    public function toChunk(): array
    {
        $dataUrl = 'data:'.$this->mimeType.';base64,'.$this->base64;

        return str_starts_with($this->mimeType, 'image/')
            ? ['type' => 'image_url', 'image_url' => $dataUrl]
            : ['type' => 'document_url', 'document_url' => $dataUrl];
    }
}
