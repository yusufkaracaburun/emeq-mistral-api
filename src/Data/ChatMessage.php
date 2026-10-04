<?php

declare(strict_types=1);

namespace Emeq\MistralApi\Data;

final readonly class ChatMessage
{
    /**
     * @param  list<Document>  $attachments
     */
    private function __construct(
        private string $role,
        private string $text,
        private array $attachments,
    ) {}

    public static function user(string $text, Document ...$attachments): self
    {
        return new self('user', $text, array_values($attachments));
    }

    /**
     * @return array{role: string, content: string|list<array<string, string>>}
     */
    public function toArray(): array
    {
        if ($this->attachments === []) {
            return ['role' => $this->role, 'content' => $this->text];
        }

        return [
            'role' => $this->role,
            'content' => [
                ['type' => 'text', 'text' => $this->text],
                ...array_map(static fn (Document $document): array => $document->toChunk(), $this->attachments),
            ],
        ];
    }
}
