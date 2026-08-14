<?php

declare(strict_types=1);

namespace Codepreneur\Fathom\Data;

readonly class AssigneeData extends DataTransferObject
{
    public function __construct(
        public ?string $name,
        public ?string $email,
        public ?string $team,
        array $raw = [],
    ) {
        parent::__construct($raw);

    }

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'] ?? null,
            email: $data['email'] ?? null,
            team: $data['team'] ?? null,
            raw: $data,
        );
    }
}
