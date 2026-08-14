<?php

declare(strict_types=1);

namespace Codepreneur\Fathom\Data;

readonly class TranscriptData extends DataTransferObject
{
    /**
     * @param  array<int, TranscriptEntryData>  $entries
     */
    public function __construct(
        public array $entries,
        public ?string $destinationUrl = null,
        array $raw = [],
    ) {
        parent::__construct($raw);

    }

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        if (isset($data['destination_url'])) {
            return new self(
                entries: [],
                destinationUrl: $data['destination_url'],
                raw: $data,
            );
        }

        $entries = array_map(
            fn (array $entry): TranscriptEntryData => TranscriptEntryData::fromArray($entry),
            $data['transcript'] ?? []
        );

        return new self(
            entries: $entries,
            raw: $data,
        );
    }

    public function isAsync(): bool
    {
        return $this->destinationUrl !== null;
    }
}
