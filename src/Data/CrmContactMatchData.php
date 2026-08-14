<?php

declare(strict_types=1);

namespace Codepreneur\Fathom\Data;

readonly class CrmContactMatchData extends DataTransferObject
{
    public function __construct(
        public string $name,
        public string $email,
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
            email: (string) ($data['email'] ?? ''),
            recordUrl: (string) ($data['record_url'] ?? ''),
            raw: $data,
        );
    }
}
