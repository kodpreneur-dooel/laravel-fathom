<?php

declare(strict_types=1);

namespace Codepreneur\Fathom\Data;

readonly class HighlightData extends DataTransferObject
{
    public function __construct(
        public string $type,
        public ?string $summary,
        public ?string $text,
        public float $startTime,
        public float $endTime,
        array $raw = [],
    ) {
        parent::__construct($raw);

    }

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            type: (string) ($data['type'] ?? ''),
            summary: $data['summary'] ?? null,
            text: $data['text'] ?? null,
            startTime: (float) ($data['start_time'] ?? 0),
            endTime: (float) ($data['end_time'] ?? 0),
            raw: $data,
        );
    }
}
