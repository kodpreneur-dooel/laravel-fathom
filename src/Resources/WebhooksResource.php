<?php

declare(strict_types=1);

namespace Codepreneur\Fathom\Resources;

use Codepreneur\Fathom\Data\MeetingData;
use Codepreneur\Fathom\Data\WebhookData;
use Codepreneur\Fathom\Events\FathomMeetingReceived;
use Codepreneur\Fathom\Events\FathomWebhookReceived;
use Codepreneur\Fathom\FathomClient;
use Codepreneur\Fathom\Support\WebhookSignature;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;

class WebhooksResource
{
    public function __construct(
        protected FathomClient $client,
        protected ?string $webhookSecret = null,
        protected int $tolerance = 300,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): WebhookData
    {
        $response = $this->client->post('webhooks', $data);

        return WebhookData::fromArray($response);
    }

    public function delete(string $webhookId): void
    {
        $this->client->delete("webhooks/{$webhookId}");
    }

    public function verify(Request $request, ?string $secret = null): bool
    {
        $secret ??= $this->webhookSecret;

        if ($secret === null || $secret === '') {
            return false;
        }

        return WebhookSignature::verify(
            $secret,
            $this->extractHeaders($request),
            $request->getContent(),
            $this->tolerance
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function payload(Request $request): array
    {
        $payload = $request->json()->all();

        if (! is_array($payload)) {
            return [];
        }

        return $payload;
    }

    public function meeting(Request $request): MeetingData
    {
        return MeetingData::fromArray($this->payload($request));
    }

    public function dispatchEvents(Request $request): void
    {
        $payload = $this->payload($request);

        Event::dispatch(new FathomWebhookReceived($payload, $request));

        if ($this->isMeetingPayload($payload)) {
            Event::dispatch(new FathomMeetingReceived(
                MeetingData::fromArray($payload),
                $request
            ));
        }
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    protected function isMeetingPayload(array $payload): bool
    {
        return isset($payload['recording_id']);
    }

    /**
     * @return array<string, string>
     */
    protected function extractHeaders(Request $request): array
    {
        $headers = [];

        foreach (['webhook-id', 'webhook-timestamp', 'webhook-signature'] as $header) {
            $value = $request->header($header);

            if ($value !== null) {
                $headers[$header] = $value;
            }
        }

        return $headers;
    }
}
