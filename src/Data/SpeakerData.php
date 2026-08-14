<?php

declare(strict_types=1);

namespace Codepreneur\Fathom\Data;

readonly class SpeakerData extends DataTransferObject
{
    public function __construct(
        public string $displayName,
        public ?string $matchedCalendarInviteeEmail,
        array $raw = [],
    ) {
        parent::__construct($raw);

    }

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            displayName: (string) ($data['display_name'] ?? ''),
            matchedCalendarInviteeEmail: $data['matched_calendar_invitee_email'] ?? null,
            raw: $data,
        );
    }
}
