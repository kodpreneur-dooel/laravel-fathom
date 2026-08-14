<?php

declare(strict_types=1);

namespace Codepreneur\Fathom\Data;

use Carbon\CarbonImmutable;

readonly class UserData extends DataTransferObject
{
    public function __construct(
        public ?string $name,
        public string $email,
        public CarbonImmutable $createdAt,
        public string $status,
        public ?UserPermissionsData $permissions,
        array $raw = [],
    ) {
        parent::__construct($raw);

    }

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'] ?? null,
            email: (string) ($data['email'] ?? ''),
            createdAt: self::parseDate($data['created_at'] ?? null) ?? CarbonImmutable::now(),
            status: (string) ($data['status'] ?? ''),
            permissions: isset($data['permissions']) ? UserPermissionsData::fromArray($data['permissions']) : null,
            raw: $data,
        );
    }
}
