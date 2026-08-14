<?php

declare(strict_types=1);

namespace Codepreneur\Fathom\Data;

readonly class CrmDealMatchData extends DataTransferObject
{
    public function __construct(
        public string $name,
        public float $amount,
        public string $recordUrl,
        array $raw = [],
    ) {
        parent::__construct($raw);

    }

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            name: (string) ($data['name'] ?? ''),
            amount: (float) ($data['amount'] ?? 0),
            recordUrl: (string) ($data['record_url'] ?? ''),
            raw: $data,
        );
    }
}
