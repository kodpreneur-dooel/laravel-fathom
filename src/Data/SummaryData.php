<?php

declare(strict_types=1);

namespace Codepreneur\Fathom\Data;

readonly class SummaryData extends DataTransferObject
{
    public function __construct(
        public ?string $templateName,
        public ?string $markdown,
        public ?string $destinationUrl = null,
        array $raw = [],
    ) {
        parent::__construct($raw);

    }

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        if (isset($data['destination_url'])) {
            return new self(
                templateName: null,
                markdown: null,
                destinationUrl: $data['destination_url'],
                raw: $data,
            );
        }

        $summary = $data['summary'] ?? $data;

        return new self(
            templateName: $summary['template_name'] ?? null,
            markdown: $summary['markdown_formatted'] ?? null,
            raw: $data,
        );
    }

    public function isAsync(): bool
    {
        return $this->destinationUrl !== null;
    }
}
