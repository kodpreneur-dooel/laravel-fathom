# Laravel Fathom

A Laravel SDK for the [Fathom AI API](https://developers.fathom.ai/).

[![Tests](https://github.com/kodpreneur-dooel/laravel-fathom/actions/workflows/tests.yml/badge.svg)](https://github.com/kodpreneur-dooel/laravel-fathom/actions/workflows/tests.yml)
[![License: MIT](https://img.shields.io/badge/License-MIT-blue.svg)](LICENSE)

Open-source Laravel SDK for the Fathom AI API — meetings, transcripts, summaries and webhooks.

## Features

- Laravel-native facade and service provider with auto-discovery
- Typed DTOs with forward-compatible `raw()` access
- Cursor pagination support
- Webhook signature verification and middleware
- Rate limit exception with header details
- Comprehensive offline test suite
- No database dependency — storage-agnostic SDK

## Requirements

- PHP 8.2+ (PHP 8.3+ required for Laravel 13)
- Laravel 11, 12, or 13

## Installation

```bash
composer require codepreneur/laravel-fathom
```

Publish the configuration (optional):

```bash
php artisan vendor:publish --tag=fathom-config
```

## Fathom API Key

Generate an API key in your [Fathom Settings → API Access](https://fathom.video/customize#api-access-header).

## Configuration

Add to your `.env`:

```env
FATHOM_API_KEY=your-api-key
FATHOM_WEBHOOK_SECRET=whsec_your-webhook-secret
```

Configuration options in `config/fathom.php`:

| Key | Description | Default |
|-----|-------------|---------|
| `api_key` | Your Fathom API key | — |
| `base_url` | API base URL | `https://api.fathom.ai/external/v1` |
| `timeout` | HTTP timeout in seconds | `30` |
| `webhook.secret` | Webhook signing secret | — |
| `webhook.tolerance` | Signature timestamp tolerance (seconds) | `300` |

## Basic Usage

```php
use Codepreneur\Fathom\Facades\Fathom;

$meetings = Fathom::meetings()->list();
$transcript = Fathom::recordings()->transcript($recordingId);
$summary = Fathom::recordings()->summary($recordingId);
```

## Meetings

```php
$page = Fathom::meetings()->list();

foreach ($page->items as $meeting) {
    echo $meeting->title;
    echo $meeting->recordingId;
    echo $meeting->recordedBy->email;
}
```

### Meeting Filtering

```php
$page = Fathom::meetings()->list([
    'recorded_by' => ['ceo@acme.com'],
    'teams' => ['Sales', 'Engineering'],
    'created_after' => '2025-01-01T00:00:00Z',
    'created_before' => '2025-12-31T23:59:59Z',
    'meeting_type' => 'Quarterly Business Review',
    'calendar_invitees_domains_type' => 'one_or_more_external',
    'include_transcript' => true,
    'include_summary' => true,
    'include_action_items' => true,
    'include_highlights' => true,
    'include_crm_matches' => true,
]);
```

### Meeting Types

```php
$types = Fathom::meetings()->types();
```

## Pagination

Fathom uses cursor-based pagination:

```php
$page = Fathom::meetings()->list();

foreach ($page->items as $meeting) {
    // process meeting
}

if ($page->hasMore()) {
    $nextPage = Fathom::meetings()->next($page);
}
```

## Transcripts

```php
$transcript = Fathom::recordings()->transcript($recordingId);

foreach ($transcript->entries as $entry) {
    echo $entry->speaker->displayName;
    echo $entry->speaker->matchedCalendarInviteeEmail;
    echo $entry->text;
    echo $entry->timestamp; // e.g. "00:05:32"
}
```

For async delivery, pass a `destination_url`:

```php
$transcript = Fathom::recordings()->transcript($recordingId, [
    'destination_url' => 'https://example.com/callback',
]);
```

## Summaries

```php
$summary = Fathom::recordings()->summary($recordingId);

echo $summary->templateName;
echo $summary->markdown;
```

## Recording Downloads

```php
$download = Fathom::recordings()->requestDownload($recordingId);

while ($download->isProcessing()) {
    sleep(2);
    $download = Fathom::recordings()->downloadStatus($recordingId, $download->downloadId);
}

if ($download->isCompleted()) {
    $url = $download->video?->url ?? $download->audio?->url;
}
```

## Teams

```php
$teams = Fathom::teams()->list();

foreach ($teams->items as $team) {
    echo $team->name;
}
```

## Team Members

```php
$members = Fathom::teamMembers()->list(['team' => 'Sales']);

foreach ($members->items as $member) {
    echo $member->name;
    echo $member->email;
}
```

## Users

Admin-only endpoint for listing users and permissions:

```php
$users = Fathom::users()->list();
```

## Webhooks

### Create a Webhook

```php
$webhook = Fathom::webhooks()->create([
    'destination_url' => 'https://example.com/webhooks/fathom',
    'triggered_for' => ['my_recordings'],
    'include_transcript' => true,
    'include_summary' => true,
    'include_action_items' => true,
]);

// Store the secret securely — it is only returned once
$webhook->id;
$webhook->secret;
```

Available `triggered_for` values:

- `my_recordings`
- `shared_external_recordings`
- `my_shared_with_team_recordings` (Team Plans)
- `shared_team_recordings` (Team Plans)

### Delete a Webhook

```php
Fathom::webhooks()->delete($webhookId);
```

## Webhook Verification

Fathom signs webhooks using HMAC SHA-256. Verify incoming requests:

```php
if (Fathom::webhooks()->verify($request)) {
    $meeting = Fathom::webhooks()->meeting($request);
}
```

Parse webhook payloads:

```php
$payload = Fathom::webhooks()->payload($request);
$meeting = Fathom::webhooks()->meeting($request);
```

## Middleware

Register the middleware alias in your routes:

```php
use App\Http\Controllers\FathomWebhookController;

Route::post('/webhooks/fathom', FathomWebhookController::class)
    ->middleware('fathom.webhook');
```

## Events

Listen for webhook events:

```php
use Codepreneur\Fathom\Events\FathomMeetingReceived;
use Codepreneur\Fathom\Events\FathomWebhookReceived;

Event::listen(FathomMeetingReceived::class, StoreMeeting::class);
Event::listen(FathomWebhookReceived::class, LogWebhook::class);
```

Dispatch events manually:

```php
Fathom::webhooks()->dispatchEvents($request);
```

## Exceptions

| Exception | HTTP Status |
|-----------|-------------|
| `AuthenticationException` | 401, 403 |
| `NotFoundException` | 404 |
| `ValidationException` | 422 |
| `RateLimitException` | 429 |
| `FathomException` | Other |

Handle rate limits:

```php
use Codepreneur\Fathom\Exceptions\RateLimitException;

try {
    Fathom::meetings()->list();
} catch (RateLimitException $e) {
    $e->limit();       // 60
    $e->remaining();   // 0
    $e->reset();       // seconds remaining in window
    $e->retryAfter();  // seconds to wait
    $e->resetAt();     // CarbonImmutable
}
```

## Rate Limits

Fathom enforces the following limits:

| Type | Limit |
|------|-------|
| Global | 60 requests / 60 seconds |
| Heavy (transcript, summary, includes) | 30 requests / 60 seconds |
| Recording downloads | 30 requests / 60 seconds |

This package does not automatically retry rate-limited requests.

## DTOs and Forward Compatibility

All responses are mapped to typed DTOs. Access unknown future API fields via `raw()`:

```php
$meeting = Fathom::meetings()->list()->items[0];
$meeting->title;              // typed property
$meeting->raw()['new_field']; // forward-compatible access
```

## Testing

The package includes a full offline test suite using `Http::fake()`:

```bash
composer test
composer analyse
composer lint
```

## Contributing

See [CONTRIBUTING.md](CONTRIBUTING.md).

## Security

See [SECURITY.md](SECURITY.md).

## Changelog

See [CHANGELOG.md](CHANGELOG.md).

## License

The MIT License (MIT). See [LICENSE](LICENSE) for details.
