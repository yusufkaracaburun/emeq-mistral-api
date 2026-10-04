<?php

declare(strict_types=1);

namespace Emeq\MistralApi\Data;

final readonly class OcrResult
{
    /**
     * @param  list<OcrPage>  $pages
     */
    public function __construct(
        public string $model,
        public array $pages,
        public int $pagesProcessed,
    ) {}

    /**
     * @param  array<array-key, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $usage = is_array($data['usage_info'] ?? null) ? $data['usage_info'] : [];

        return new self(
            model: (string) ($data['model'] ?? ''),
            pages: array_values(array_map(OcrPage::fromArray(...), array_filter($data['pages'] ?? [], is_array(...)))),
            pagesProcessed: (int) ($usage['pages_processed'] ?? 0),
        );
    }
}
