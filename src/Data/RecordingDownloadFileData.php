<?php

declare(strict_types=1);

namespace Codepreneur\Fathom\Data;

use Carbon\CarbonImmutable;

readonly class RecordingDownloadFileData extends DataTransferObject
{
    public function __construct(
        public string $url,
        public string $contentType,
        public int $fileSizeBytes,
        public CarbonImmutable $expiresAt,
        array $raw = [],
    ) {
        parent::__construct($raw);

    }

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            url: (string) ($data['url'] ?? ''),
            contentType: (string) ($data['content_type'] ?? ''),
            fileSizeBytes: (int) ($data['file_size_bytes'] ?? 0),
            expiresAt: self::parseDate($data['expires_at'] ?? null) ?? CarbonImmutable::now(),
            raw: $data,
        );
    }
}
