<?php

declare(strict_types=1);

namespace Codepreneur\Fathom\Tests\Feature;

use Codepreneur\Fathom\Data\MeetingData;
use Codepreneur\Fathom\Events\FathomMeetingReceived;
use Codepreneur\Fathom\Events\FathomWebhookReceived;
use Codepreneur\Fathom\Facades\Fathom;
use Codepreneur\Fathom\Http\Middleware\VerifyFathomWebhook;
use Codepreneur\Fathom\Support\WebhookSignature;
use Codepreneur\Fathom\Tests\TestCase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;

class WebhookTest extends TestCase
{
    protected string $webhookSecret = 'whsec_5WbX5kEWLlfzsGNjH64I8lOOqUB6e8FH';

    public function test_webhook_signature_verification_valid(): void
    {
        $body = json_encode(['recording_id' => 123, 'title' => 'Test Meeting']);
        $timestamp = (string) time();
        $webhookId = 'msg_test123';

        $signature = $this->generateSignature($webhookId, $timestamp, $body);

        $request = Request::create('/webhooks/fathom', 'POST', [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_webhook-id' => $webhookId,
            'HTTP_webhook-timestamp' => $timestamp,
            'HTTP_webhook-signature' => "v1,{$signature}",
        ], $body);

        $this->assertTrue(Fathom::webhooks()->verify($request));
    }

    public function test_webhook_signature_verification_invalid(): void
    {
        $body = json_encode(['recording_id' => 123]);
        $timestamp = (string) time();

        $request = Request::create('/webhooks/fathom', 'POST', [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_webhook-id' => 'msg_test123',
            'HTTP_webhook-timestamp' => $timestamp,
            'HTTP_webhook-signature' => 'v1,invalidsignature==',
        ], $body);

        $this->assertFalse(Fathom::webhooks()->verify($request));
    }

    public function test_webhook_signature_verification_expired_timestamp(): void
    {
        $body = json_encode(['recording_id' => 123]);
        $timestamp = (string) (time() - 600);
        $webhookId = 'msg_test123';

        $signature = $this->generateSignature($webhookId, $timestamp, $body);

        $request = Request::create('/webhooks/fathom', 'POST', [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_webhook-id' => $webhookId,
            'HTTP_webhook-timestamp' => $timestamp,
            'HTTP_webhook-signature' => "v1,{$signature}",
        ], $body);

        $this->assertFalse(Fathom::webhooks()->verify($request));
    }

    public function test_webhook_payload_parsing(): void
    {
        $payload = [
            'title' => 'Test Meeting',
            'recording_id' => 123,
            'meeting_title' => 'Test',
            'meeting_type' => null,
            'url' => 'https://fathom.video/1',
            'share_url' => 'https://fathom.video/share/1',
            'created_at' => '2025-03-01T17:01:30Z',
            'scheduled_start_time' => '2025-03-01T16:00:00Z',
            'scheduled_end_time' => '2025-03-01T17:00:00Z',
            'recording_start_time' => '2025-03-01T16:01:12Z',
            'recording_end_time' => '2025-03-01T17:00:55Z',
            'calendar_invitees_domains_type' => 'only_internal',
            'shared_with' => 'no_teams',
            'transcript_language' => 'en',
            'recorded_by' => [
                'name' => 'Alice',
                'email' => 'alice@acme.com',
                'email_domain' => 'acme.com',
                'team' => null,
            ],
            'calendar_invitees' => [],
        ];

        $request = Request::create('/webhooks/fathom', 'POST', [], [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode($payload));

        $parsed = Fathom::webhooks()->payload($request);
        $this->assertEquals('Test Meeting', $parsed['title']);

        $meeting = Fathom::webhooks()->meeting($request);
        $this->assertInstanceOf(MeetingData::class, $meeting);
        $this->assertEquals(123, $meeting->recordingId);
    }

    public function test_webhook_payload_without_transcript(): void
    {
        $payload = [
            'title' => 'Test Meeting',
            'recording_id' => 123,
            'meeting_title' => 'Test',
            'meeting_type' => null,
            'url' => 'https://fathom.video/1',
            'share_url' => 'https://fathom.video/share/1',
            'created_at' => '2025-03-01T17:01:30Z',
            'scheduled_start_time' => '2025-03-01T16:00:00Z',
            'scheduled_end_time' => '2025-03-01T17:00:00Z',
            'recording_start_time' => '2025-03-01T16:01:12Z',
            'recording_end_time' => '2025-03-01T17:00:55Z',
            'calendar_invitees_domains_type' => 'only_internal',
            'shared_with' => 'no_teams',
            'transcript_language' => 'en',
            'recorded_by' => [
                'name' => 'Alice',
                'email' => 'alice@acme.com',
                'email_domain' => 'acme.com',
                'team' => null,
            ],
            'calendar_invitees' => [],
        ];

        $request = Request::create('/webhooks/fathom', 'POST', [], [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode($payload));

        $meeting = Fathom::webhooks()->meeting($request);

        $this->assertNull($meeting->transcript);
        $this->assertNull($meeting->defaultSummary);
    }

    public function test_middleware_rejects_invalid_signature(): void
    {
        Route::post('/webhooks/fathom', fn () => response('OK'))
            ->middleware(VerifyFathomWebhook::class);

        $response = $this->postJson('/webhooks/fathom', ['recording_id' => 123]);

        $response->assertForbidden();
    }

    public function test_middleware_accepts_valid_signature(): void
    {
        Route::post('/webhooks/fathom', fn () => response('OK'))
            ->middleware(VerifyFathomWebhook::class);

        $body = json_encode(['recording_id' => 123]);
        $timestamp = (string) time();
        $webhookId = 'msg_test123';
        $signature = $this->generateSignature($webhookId, $timestamp, $body);

        $response = $this->call(
            'POST',
            '/webhooks/fathom',
            [],
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_webhook-id' => $webhookId,
                'HTTP_webhook-timestamp' => $timestamp,
                'HTTP_webhook-signature' => "v1,{$signature}",
            ],
            $body
        );

        $response->assertOk();
    }

    public function test_webhook_events_are_dispatched(): void
    {
        Event::fake([FathomWebhookReceived::class, FathomMeetingReceived::class]);

        $payload = [
            'title' => 'Test',
            'recording_id' => 123,
            'meeting_title' => 'Test',
            'meeting_type' => null,
            'url' => 'https://fathom.video/1',
            'share_url' => 'https://fathom.video/share/1',
            'created_at' => '2025-03-01T17:01:30Z',
            'scheduled_start_time' => '2025-03-01T16:00:00Z',
            'scheduled_end_time' => '2025-03-01T17:00:00Z',
            'recording_start_time' => '2025-03-01T16:01:12Z',
            'recording_end_time' => '2025-03-01T17:00:55Z',
            'calendar_invitees_domains_type' => 'only_internal',
            'shared_with' => 'no_teams',
            'transcript_language' => 'en',
            'recorded_by' => [
                'name' => 'Alice',
                'email' => 'alice@acme.com',
                'email_domain' => 'acme.com',
                'team' => null,
            ],
            'calendar_invitees' => [],
        ];

        $request = Request::create('/webhooks/fathom', 'POST', [], [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode($payload));

        Fathom::webhooks()->dispatchEvents($request);

        Event::assertDispatched(FathomWebhookReceived::class);
        Event::assertDispatched(FathomMeetingReceived::class);
    }

    public function test_webhook_signature_support_class(): void
    {
        $body = '{"test": true}';
        $timestamp = (string) time();
        $webhookId = 'msg_abc';

        $signature = $this->generateSignature($webhookId, $timestamp, $body);

        $result = WebhookSignature::verify(
            $this->webhookSecret,
            [
                'webhook-id' => $webhookId,
                'webhook-timestamp' => $timestamp,
                'webhook-signature' => "v1,{$signature}",
            ],
            $body,
            300
        );

        $this->assertTrue($result);
    }

    protected function generateSignature(string $webhookId, string $timestamp, string $body): string
    {
        $signedContent = "{$webhookId}.{$timestamp}.{$body}";
        $secretBytes = base64_decode(explode('_', $this->webhookSecret, 2)[1]);

        return base64_encode(
            hash_hmac('sha256', $signedContent, $secretBytes, true)
        );
    }
}
