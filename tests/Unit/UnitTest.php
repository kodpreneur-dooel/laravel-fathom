<?php

declare(strict_types=1);

namespace Codepreneur\Fathom\Tests\Unit;

use Codepreneur\Fathom\Authentication\ApiKeyAuthenticator;
use Codepreneur\Fathom\Data\MeetingData;
use Codepreneur\Fathom\Data\SummaryData;
use Codepreneur\Fathom\Exceptions\FathomException;
use Codepreneur\Fathom\Tests\TestCase;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;

class UnitTest extends TestCase
{
    public function test_api_key_authenticator_adds_header(): void
    {
        $authenticator = new ApiKeyAuthenticator('my-secret-key');

        $request = $authenticator->authenticate(new PendingRequest);

        $this->assertEquals(['X-Api-Key' => 'my-secret-key'], $request->getOptions()['headers'] ?? []);
    }

    public function test_fathom_exception_sanitizes_sensitive_headers(): void
    {
        Http::fake([
            '*' => Http::response(['error' => 'fail'], 500, [
                'X-Api-Key' => 'secret-key',
                'Authorization' => 'Bearer token',
                'Content-Type' => 'application/json',
            ]),
        ]);

        try {
            Http::get('https://api.fathom.ai/external/v1/meetings')->throw();
        } catch (RequestException $e) {
            $exception = new FathomException('Error', 500, $e, $e->response);

            $headers = $exception->headers();

            $this->assertArrayNotHasKey('X-Api-Key', $headers);
            $this->assertArrayNotHasKey('Authorization', $headers);
            $this->assertArrayHasKey('Content-Type', $headers);
        }
    }

    public function test_meeting_data_from_array_with_unknown_fields(): void
    {
        $data = [
            'title' => 'Test',
            'meeting_title' => 'Test',
            'meeting_type' => null,
            'recording_id' => 1,
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
            'brand_new_field' => 'future proof',
        ];

        $meeting = MeetingData::fromArray($data);

        $this->assertEquals('future proof', $meeting->raw()['brand_new_field']);
    }

    public function test_summary_data_async_response(): void
    {
        $summary = SummaryData::fromArray([
            'destination_url' => 'https://example.com/callback',
        ]);

        $this->assertTrue($summary->isAsync());
        $this->assertNull($summary->templateName);
    }
}
