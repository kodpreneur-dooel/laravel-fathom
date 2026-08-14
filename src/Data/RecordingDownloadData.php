<?php

declare(strict_types=1);

namespace Codepreneur\Fathom\Data;

readonly class RecordingDownloadData extends DataTransferObject
{
    public function __construct(
        public string $downloadId,
        public int $recordingId,
        public string $status,
        public ?RecordingDownloadFileData $video = null,
        public ?RecordingDownloadFileData $audio = null,
        public ?string $failureReason = null,
        array $raw = [],
    ) {
        parent::__construct($raw);

    }

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            downloadId: (string) ($data['download_id'] ?? ''),
            recordingId: (int) ($data['recording_id'] ?? 0),
            status: (string) ($data['status'] ?? ''),
            video: isset($data['video']) ? RecordingDownloadFileData::fromArray($data['video']) : null,
            audio: isset($data['audio']) ? RecordingDownloadFileData::fromArray($data['audio']) : null,
            failureReason: $data['failure_reason'] ?? null,
            raw: $data,
        );
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isProcessing(): bool
    {
        return $this->status === 'processing';
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }
}
