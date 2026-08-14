<?php

declare(strict_types=1);

namespace Codepreneur\Fathom\Data;

use Carbon\CarbonImmutable;

readonly class MeetingData extends DataTransferObject
{
    /**
     * @param  array<int, InviteeData>  $calendarInvitees
     * @param  array<int, TranscriptEntryData>|null  $transcript
     * @param  array<int, ActionItemData>|null  $actionItems
     * @param  array<int, HighlightData>|null  $highlights
     */
    public function __construct(
        public string $title,
        public ?string $meetingTitle,
        public ?string $meetingType,
        public int $recordingId,
        public string $url,
        public ?string $meetingUrl,
        public string $shareUrl,
        public CarbonImmutable $createdAt,
        public CarbonImmutable $scheduledStartTime,
        public CarbonImmutable $scheduledEndTime,
        public CarbonImmutable $recordingStartTime,
        public CarbonImmutable $recordingEndTime,
        public string $calendarInviteesDomainsType,
        public string $sharedWith,
        public string $transcriptLanguage,
        public FathomUserData $recordedBy,
        public array $calendarInvitees,
        public ?SummaryData $defaultSummary = null,
        public ?array $transcript = null,
        public ?array $actionItems = null,
        public ?array $highlights = null,
        public ?CrmMatchesData $crmMatches = null,
        array $raw = [],
    ) {
        parent::__construct($raw);

    }

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        $transcript = null;
        if (isset($data['transcript']) && is_array($data['transcript'])) {
            $transcript = array_map(
                fn (array $entry): TranscriptEntryData => TranscriptEntryData::fromArray($entry),
                $data['transcript']
            );
        }

        $actionItems = null;
        if (isset($data['action_items']) && is_array($data['action_items'])) {
            $actionItems = array_map(
                fn (array $item): ActionItemData => ActionItemData::fromArray($item),
                $data['action_items']
            );
        }

        $highlights = null;
        if (isset($data['highlights']) && is_array($data['highlights'])) {
            $highlights = array_map(
                fn (array $item): HighlightData => HighlightData::fromArray($item),
                $data['highlights']
            );
        }

        $calendarInvitees = array_map(
            fn (array $invitee): InviteeData => InviteeData::fromArray($invitee),
            $data['calendar_invitees'] ?? []
        );

        return new self(
            title: (string) ($data['title'] ?? ''),
            meetingTitle: $data['meeting_title'] ?? null,
            meetingType: $data['meeting_type'] ?? null,
            recordingId: (int) ($data['recording_id'] ?? 0),
            url: (string) ($data['url'] ?? ''),
            meetingUrl: $data['meeting_url'] ?? null,
            shareUrl: (string) ($data['share_url'] ?? ''),
            createdAt: self::parseDate($data['created_at'] ?? null) ?? CarbonImmutable::now(),
            scheduledStartTime: self::parseDate($data['scheduled_start_time'] ?? null) ?? CarbonImmutable::now(),
            scheduledEndTime: self::parseDate($data['scheduled_end_time'] ?? null) ?? CarbonImmutable::now(),
            recordingStartTime: self::parseDate($data['recording_start_time'] ?? null) ?? CarbonImmutable::now(),
            recordingEndTime: self::parseDate($data['recording_end_time'] ?? null) ?? CarbonImmutable::now(),
            calendarInviteesDomainsType: (string) ($data['calendar_invitees_domains_type'] ?? ''),
            sharedWith: (string) ($data['shared_with'] ?? ''),
            transcriptLanguage: (string) ($data['transcript_language'] ?? ''),
            recordedBy: FathomUserData::fromArray($data['recorded_by'] ?? []),
            calendarInvitees: $calendarInvitees,
            defaultSummary: isset($data['default_summary']) ? SummaryData::fromArray(['summary' => $data['default_summary']]) : null,
            transcript: $transcript,
            actionItems: $actionItems,
            highlights: $highlights,
            crmMatches: isset($data['crm_matches']) ? CrmMatchesData::fromArray($data['crm_matches']) : null,
            raw: $data,
        );
    }
}
