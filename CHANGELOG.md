# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added

- Laravel 13 support (`illuminate/support` and `illuminate/http` ^13.0)

### Added (initial release)

- Initial release of the Laravel Fathom SDK
- API key authentication via `X-Api-Key` header
- Meetings API with filtering, includes, and cursor pagination
- Meeting types listing
- Recordings API: transcript, summary, and download endpoints
- Teams and team members listing
- Users listing (admin only)
- Webhook creation and deletion
- Webhook signature verification (HMAC SHA-256)
- `VerifyFathomWebhook` middleware
- Typed DTOs with `raw()` forward compatibility
- Laravel events: `FathomWebhookReceived`, `FathomMeetingReceived`
- Rate limit exception with header exposure
