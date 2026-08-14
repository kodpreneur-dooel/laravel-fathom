# Security Policy

## Supported Versions

| Version | Supported          |
| ------- | ------------------ |
| 0.x     | :white_check_mark: |
| 1.x     | :white_check_mark: |

## Reporting a Vulnerability

If you discover a security vulnerability in this package, please report it responsibly.

**Do not open a public GitHub issue for security vulnerabilities.**

Instead, please email the maintainers with:

- A description of the vulnerability
- Steps to reproduce
- Potential impact

We will acknowledge your report within 48 hours and provide an estimated timeline for a fix.

## Security Best Practices

When using this package:

- Store `FATHOM_API_KEY` and `FATHOM_WEBHOOK_SECRET` in environment variables
- Never commit API keys or webhook secrets to version control
- Always verify webhook signatures in production using the `fathom.webhook` middleware
- Rotate API keys and webhook secrets periodically
