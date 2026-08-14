<?php

declare(strict_types=1);

namespace Codepreneur\Fathom\Data;

use Carbon\CarbonImmutable;

readonly class WebhookData extends DataTransferObject
{
    /**
     * @param  array<int, string>  $triggeredFor
     */
    public function __construct(
        public string $id,
        public string $url,
        public string $secret,
        public CarbonImmutable $createdAt,
        public bool $includeTranscript,
        public bool $includeCrmMatches,
        public bool $includeSummary,
        public bool $includeActionItems,
        public array $triggeredFor,
        array $raw = [],
    ) {
        parent::__construct($raw);

    }

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            id: (string) ($data['id'] ?? ''),
            url: (string) ($data['url'] ?? ''),
            secret: (string) ($data['secret'] ?? ''),
            createdAt: self::parseDate($data['created_at'] ?? null) ?? CarbonImmutable::now(),
            includeTranscript: (bool) ($data['include_transcript'] ?? false),
            includeCrmMatches: (bool) ($data['include_crm_matches'] ?? false),
            includeSummary: (bool) ($data['include_summary'] ?? false),
            includeActionItems: (bool) ($data['include_action_items'] ?? false),
            triggeredFor: $data['triggered_for'] ?? [],
            raw: $data,
        );
    }
}
