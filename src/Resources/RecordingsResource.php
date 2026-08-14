<?php

declare(strict_types=1);

namespace Codepreneur\Fathom\Resources;

use Codepreneur\Fathom\Data\RecordingDownloadData;
use Codepreneur\Fathom\Data\SummaryData;
use Codepreneur\Fathom\Data\TranscriptData;
use Codepreneur\Fathom\FathomClient;

class RecordingsResource
{
    public function __construct(
        protected FathomClient $client,
    ) {}

    /**
     * @param  array<string, mixed>  $options
     */
    public function transcript(int $recordingId, array $options = []): TranscriptData
    {
        $data = $this->client->get("recordings/{$recordingId}/transcript", $options);

        return TranscriptData::fromArray($data);
    }

    /**
     * @param  array<string, mixed>  $options
     */
    public function summary(int $recordingId, array $options = []): SummaryData
    {
        $data = $this->client->get("recordings/{$recordingId}/summary", $options);

        return SummaryData::fromArray($data);
    }

    /**
     * @param  array<string, mixed>  $options
     */
    public function requestDownload(int $recordingId, array $options = []): RecordingDownloadData
    {
        $data = $this->client->post("recordings/{$recordingId}/download", $options);

        return RecordingDownloadData::fromArray($data);
    }

    public function downloadStatus(int $recordingId, string $downloadId): RecordingDownloadData
    {
        $data = $this->client->get("recordings/{$recordingId}/downloads/{$downloadId}");

        return RecordingDownloadData::fromArray($data);
    }
}
