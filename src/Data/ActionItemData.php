<?php

declare(strict_types=1);

namespace Codepreneur\Fathom\Data;

readonly class ActionItemData extends DataTransferObject
{
    public function __construct(
        public string $description,
        public bool $userGenerated,
        public bool $completed,
        public string $recordingTimestamp,
        public string $recordingPlaybackUrl,
        public ?AssigneeData $assignee,
        array $raw = [],
    ) {
        parent::__construct($raw);

    }

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            description: (string) ($data['description'] ?? ''),
            userGenerated: (bool) ($data['user_generated'] ?? false),
            completed: (bool) ($data['completed'] ?? false),
            recordingTimestamp: (string) ($data['recording_timestamp'] ?? ''),
            recordingPlaybackUrl: (string) ($data['recording_playback_url'] ?? ''),
            assignee: isset($data['assignee']) ? AssigneeData::fromArray($data['assignee']) : null,
            raw: $data,
        );
    }
}
