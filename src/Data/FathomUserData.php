<?php

declare(strict_types=1);

namespace Codepreneur\Fathom\Data;

readonly class FathomUserData extends DataTransferObject
{
    public function __construct(
        public string $name,
        public string $email,
        public string $emailDomain,
        public ?string $team,
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
            emailDomain: (string) ($data['email_domain'] ?? ''),
            team: $data['team'] ?? null,
            raw: $data,
        );
    }
}
