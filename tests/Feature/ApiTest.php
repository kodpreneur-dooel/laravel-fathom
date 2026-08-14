<?php

declare(strict_types=1);

namespace Codepreneur\Fathom\Tests\Feature;

use Codepreneur\Fathom\Data\MeetingData;
use Codepreneur\Fathom\Data\SummaryData;
use Codepreneur\Fathom\Data\TranscriptData;
use Codepreneur\Fathom\Data\WebhookData;
use Codepreneur\Fathom\Exceptions\AuthenticationException;
use Codepreneur\Fathom\Exceptions\NotFoundException;
use Codepreneur\Fathom\Exceptions\RateLimitException;
use Codepreneur\Fathom\Exceptions\ValidationException;
use Codepreneur\Fathom\Facades\Fathom;
use Codepreneur\Fathom\Tests\TestCase;
use Illuminate\Support\Facades\Http;

class ApiTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Http::preventStrayRequests();
    }

    public function test_meetings_list_sends_correct_request(): void
    {
        Http::fake([
            'api.fathom.ai/external/v1/meetings*' => Http::response([
                'limit' => 1,
                'next_cursor' => 'cursor123',
                'items' => [
                    $this->sampleMeeting(),
                ],
            ]),
        ]);

        $page = Fathom::meetings()->list();

        Http::assertSent(function ($request) {
            return $request->hasHeader('X-Api-Key', 'test-api-key')
                && str_contains($request->url(), '/meetings')
                && $request->method() === 'GET';
        });

        $this->assertTrue($page->hasMore());
        $this->assertEquals('cursor123', $page->nextCursor());
        $this->assertCount(1, $page->items);
        $this->assertInstanceOf(MeetingData::class, $page->items[0]);
        $this->assertEquals(123456789, $page->items[0]->recordingId);
    }

    public function test_meetings_list_with_filters(): void
    {
        Http::fake([
            'api.fathom.ai/external/v1/meetings*' => Http::response([
                'limit' => 1,
                'next_cursor' => null,
                'items' => [],
            ]),
        ]);

        Fathom::meetings()->list([
            'recorded_by' => ['ceo@acme.com', 'pm@acme.com'],
            'teams' => ['Sales'],
            'include_transcript' => true,
            'include_summary' => true,
            'created_after' => '2025-01-01T00:00:00Z',
            'meeting_type' => 'Quarterly Business Review',
        ]);

        Http::assertSent(function ($request) {
            $url = $request->url();

            return str_contains($url, 'recorded_by')
                && str_contains($url, 'teams')
                && str_contains($url, 'include_transcript=1')
                && str_contains($url, 'include_summary=1')
                && str_contains($url, 'created_after')
                && str_contains($url, 'meeting_type');
        });
    }

    public function test_meetings_pagination_next(): void
    {
        Http::fake([
            'api.fathom.ai/external/v1/meetings*' => Http::sequence()
                ->push(['limit' => 1, 'next_cursor' => 'page2', 'items' => [$this->sampleMeeting()]])
                ->push(['limit' => 1, 'next_cursor' => null, 'items' => []]),
        ]);

        $page1 = Fathom::meetings()->list();
        $page2 = Fathom::meetings()->next($page1);

        $this->assertTrue($page1->hasMore());
        $this->assertFalse($page2->hasMore());
    }

    public function test_meeting_types_list(): void
    {
        Http::fake([
            'api.fathom.ai/external/v1/meeting_types*' => Http::response([
                'limit' => 1,
                'next_cursor' => null,
                'items' => [
                    ['name' => 'QBR', 'status' => 'active', 'created_at' => '2023-11-10T12:00:00Z'],
                ],
            ]),
        ]);

        $page = Fathom::meetings()->types();

        $this->assertCount(1, $page->items);
        $this->assertEquals('QBR', $page->items[0]->name);
    }

    public function test_transcript_retrieval(): void
    {
        Http::fake([
            'api.fathom.ai/external/v1/recordings/123/transcript*' => Http::response([
                'transcript' => [
                    [
                        'speaker' => [
                            'display_name' => 'Jane Doe',
                            'matched_calendar_invitee_email' => 'jane@acme.com',
                        ],
                        'text' => 'Hello world',
                        'timestamp' => '00:05:32',
                    ],
                ],
            ]),
        ]);

        $transcript = Fathom::recordings()->transcript(123);

        $this->assertInstanceOf(TranscriptData::class, $transcript);
        $this->assertFalse($transcript->isAsync());
        $this->assertCount(1, $transcript->entries);
        $this->assertEquals('Jane Doe', $transcript->entries[0]->speaker->displayName);
        $this->assertEquals('jane@acme.com', $transcript->entries[0]->speaker->matchedCalendarInviteeEmail);
        $this->assertEquals('Hello world', $transcript->entries[0]->text);
        $this->assertEquals('00:05:32', $transcript->entries[0]->timestamp);
    }

    public function test_transcript_async_destination_url(): void
    {
        Http::fake([
            'api.fathom.ai/external/v1/recordings/123/transcript*' => Http::response([
                'destination_url' => 'https://example.com/callback',
            ]),
        ]);

        $transcript = Fathom::recordings()->transcript(123, [
            'destination_url' => 'https://example.com/callback',
        ]);

        $this->assertTrue($transcript->isAsync());
        $this->assertEquals('https://example.com/callback', $transcript->destinationUrl);
    }

    public function test_summary_retrieval(): void
    {
        Http::fake([
            'api.fathom.ai/external/v1/recordings/123/summary*' => Http::response([
                'summary' => [
                    'template_name' => 'general',
                    'markdown_formatted' => '## Summary\nWe reviewed Q1 OKRs.',
                ],
            ]),
        ]);

        $summary = Fathom::recordings()->summary(123);

        $this->assertInstanceOf(SummaryData::class, $summary);
        $this->assertEquals('general', $summary->templateName);
        $this->assertStringContainsString('Summary', $summary->markdown);
    }

    public function test_teams_list(): void
    {
        Http::fake([
            'api.fathom.ai/external/v1/teams*' => Http::response([
                'limit' => 10,
                'next_cursor' => null,
                'items' => [
                    ['name' => 'Sales', 'created_at' => '2023-11-10T12:00:00Z'],
                ],
            ]),
        ]);

        $page = Fathom::teams()->list();

        $this->assertCount(1, $page->items);
        $this->assertEquals('Sales', $page->items[0]->name);
    }

    public function test_team_members_list(): void
    {
        Http::fake([
            'api.fathom.ai/external/v1/team_members*' => Http::response([
                'limit' => 10,
                'next_cursor' => null,
                'items' => [
                    ['name' => 'Bob Lee', 'email' => 'bob@acme.com', 'created_at' => '2024-06-01T08:30:00Z'],
                ],
            ]),
        ]);

        $page = Fathom::teamMembers()->list(['team' => 'Sales']);

        Http::assertSent(fn ($request) => str_contains($request->url(), 'team=Sales'));

        $this->assertCount(1, $page->items);
        $this->assertEquals('Bob Lee', $page->items[0]->name);
        $this->assertEquals('bob@acme.com', $page->items[0]->email);
    }

    public function test_webhook_create(): void
    {
        Http::fake([
            'api.fathom.ai/external/v1/webhooks' => Http::response([
                'id' => 'ikEoQ4bVoq4JYUmc',
                'url' => 'https://example.com/webhook',
                'secret' => 'whsec_test123',
                'created_at' => '2025-06-30T10:40:46Z',
                'include_transcript' => true,
                'include_crm_matches' => false,
                'include_summary' => true,
                'include_action_items' => true,
                'triggered_for' => ['my_recordings'],
            ], 201),
        ]);

        $webhook = Fathom::webhooks()->create([
            'destination_url' => 'https://example.com/webhook',
            'triggered_for' => ['my_recordings'],
            'include_transcript' => true,
            'include_summary' => true,
            'include_action_items' => true,
        ]);

        $this->assertInstanceOf(WebhookData::class, $webhook);
        $this->assertEquals('ikEoQ4bVoq4JYUmc', $webhook->id);
        $this->assertEquals('whsec_test123', $webhook->secret);
    }

    public function test_webhook_delete(): void
    {
        Http::fake([
            'api.fathom.ai/external/v1/webhooks/abc123' => Http::response('', 204),
        ]);

        Fathom::webhooks()->delete('abc123');

        Http::assertSent(fn ($request) => $request->method() === 'DELETE');
    }

    public function test_authentication_exception_on_401(): void
    {
        Http::fake([
            'api.fathom.ai/external/v1/meetings*' => Http::response(['message' => 'Unauthorized'], 401),
        ]);

        $this->expectException(AuthenticationException::class);
        Fathom::meetings()->list();
    }

    public function test_authentication_exception_on_403(): void
    {
        Http::fake([
            'api.fathom.ai/external/v1/users*' => Http::response(['message' => 'Forbidden'], 403),
        ]);

        $this->expectException(AuthenticationException::class);
        Fathom::users()->list();
    }

    public function test_not_found_exception_on_404(): void
    {
        Http::fake([
            'api.fathom.ai/external/v1/recordings/999/transcript*' => Http::response(['message' => 'Not found'], 404),
        ]);

        $this->expectException(NotFoundException::class);
        Fathom::recordings()->transcript(999);
    }

    public function test_validation_exception_on_422(): void
    {
        Http::fake([
            'api.fathom.ai/external/v1/recordings/123/download' => Http::response(['message' => 'Unprocessable'], 422),
        ]);

        $this->expectException(ValidationException::class);
        Fathom::recordings()->requestDownload(123);
    }

    public function test_rate_limit_exception_on_429(): void
    {
        Http::fake([
            'api.fathom.ai/external/v1/meetings*' => Http::response(
                ['message' => 'Rate limited'],
                429,
                [
                    'RateLimit-Limit' => '60',
                    'RateLimit-Remaining' => '0',
                    'RateLimit-Reset' => '45',
                    'Retry-After' => '45',
                ]
            ),
        ]);

        try {
            Fathom::meetings()->list();
            $this->fail('Expected RateLimitException');
        } catch (RateLimitException $e) {
            $this->assertEquals(60, $e->limit());
            $this->assertEquals(0, $e->remaining());
            $this->assertEquals(45, $e->reset());
            $this->assertEquals(45, $e->retryAfter());
            $this->assertNotNull($e->resetAt());
        }
    }

    public function test_dto_preserves_raw_data(): void
    {
        Http::fake([
            'api.fathom.ai/external/v1/meetings*' => Http::response([
                'limit' => 1,
                'next_cursor' => null,
                'items' => [
                    array_merge($this->sampleMeeting(), ['future_field' => 'value']),
                ],
            ]),
        ]);

        $meeting = Fathom::meetings()->list()->items[0];

        $this->assertEquals('value', $meeting->raw()['future_field']);
    }

    public function test_dto_handles_missing_optional_fields(): void
    {
        Http::fake([
            'api.fathom.ai/external/v1/meetings*' => Http::response([
                'limit' => 1,
                'next_cursor' => null,
                'items' => [
                    [
                        'title' => 'Test',
                        'meeting_title' => null,
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
                    ],
                ],
            ]),
        ]);

        $meeting = Fathom::meetings()->list()->items[0];

        $this->assertNull($meeting->meetingTitle);
        $this->assertNull($meeting->transcript);
        $this->assertNull($meeting->defaultSummary);
    }

    /**
     * @return array<string, mixed>
     */
    protected function sampleMeeting(): array
    {
        return [
            'title' => 'Quarterly Business Review',
            'meeting_title' => 'QBR 2025 Q1',
            'meeting_type' => 'Quarterly Business Review',
            'recording_id' => 123456789,
            'url' => 'https://fathom.video/xyz123',
            'meeting_url' => 'https://us02web.zoom.us/j/123456789',
            'share_url' => 'https://fathom.video/share/xyz123',
            'created_at' => '2025-03-01T17:01:30Z',
            'scheduled_start_time' => '2025-03-01T16:00:00Z',
            'scheduled_end_time' => '2025-03-01T17:00:00Z',
            'recording_start_time' => '2025-03-01T16:01:12Z',
            'recording_end_time' => '2025-03-01T17:00:55Z',
            'calendar_invitees_domains_type' => 'one_or_more_external',
            'shared_with' => 'single_team',
            'transcript_language' => 'en',
            'recorded_by' => [
                'name' => 'Alice Johnson',
                'email' => 'alice@acme.com',
                'email_domain' => 'acme.com',
                'team' => 'Customer Success',
            ],
            'calendar_invitees' => [],
        ];
    }
}
