<?php

declare(strict_types=1);

namespace Codepreneur\Fathom\Data;

use Carbon\CarbonImmutable;

readonly class TeamMemberData extends DataTransferObject
{
    public function __construct(
        public string $name,
        public string $email,
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
            email: (string) ($data['email'] ?? ''),
            createdAt: self::parseDate($data['created_at'] ?? null) ?? CarbonImmutable::now(),
            raw: $data,
        );
    }
}
