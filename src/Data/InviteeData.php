<?php

declare(strict_types=1);

namespace Codepreneur\Fathom\Data;

readonly class InviteeData extends DataTransferObject
{
    public function __construct(
        public ?string $name,
        public ?string $email,
        public ?string $emailDomain,
        public bool $isExternal,
        public ?string $matchedSpeakerDisplayName,
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
            emailDomain: $data['email_domain'] ?? null,
            isExternal: (bool) ($data['is_external'] ?? false),
            matchedSpeakerDisplayName: $data['matched_speaker_display_name'] ?? null,
            raw: $data,
        );
    }
}
