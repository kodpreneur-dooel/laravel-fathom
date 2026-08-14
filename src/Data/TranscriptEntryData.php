<?php

declare(strict_types=1);

namespace Codepreneur\Fathom\Data;

readonly class TranscriptEntryData extends DataTransferObject
{
    public function __construct(
        public SpeakerData $speaker,
        public string $text,
        public string $timestamp,
        array $raw = [],
    ) {
        parent::__construct($raw);

    }

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            speaker: SpeakerData::fromArray($data['speaker'] ?? []),
            text: (string) ($data['text'] ?? ''),
            timestamp: (string) ($data['timestamp'] ?? ''),
            raw: $data,
        );
    }
}
