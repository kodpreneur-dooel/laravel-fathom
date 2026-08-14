<?php

declare(strict_types=1);

namespace Codepreneur\Fathom\Data;

use Carbon\CarbonImmutable;

readonly class MeetingTypeData extends DataTransferObject
{
    public function __construct(
        public string $name,
        public string $status,
        public CarbonImmutable $createdAt,
        array $raw = [],
    ) {
        parent::__construct($raw);

    }

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            name: (string) ($data['name'] ?? ''),
            status: (string) ($data['status'] ?? ''),
            createdAt: self::parseDate($data['created_at'] ?? null) ?? CarbonImmutable::now(),
            raw: $data,
        );
    }
}
