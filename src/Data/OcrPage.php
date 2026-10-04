<?php

declare(strict_types=1);

namespace Emeq\MistralApi\Data;

final readonly class OcrPage
{
    public function __construct(
        public int $index,
        public string $markdown,
    ) {}

    /**
     * @param  array<array-key, mixed>  $page
     */
    public static function fromArray(array $page): self
    {
        return new self(
            index: (int) ($page['index'] ?? 0),
            markdown: (string) ($page['markdown'] ?? ''),
        );
    }
}
