<?php

declare(strict_types=1);

namespace Codepreneur\Fathom\Support;

readonly class WebhookSignature
{
    /**
     * @param  array<string, string>  $headers
     */
    public static function verify(string $secret, array $headers, string $rawBody, int $tolerance = 300): bool
    {
        $webhookId = $headers['webhook-id'] ?? $headers['Webhook-Id'] ?? null;
        $webhookTimestamp = $headers['webhook-timestamp'] ?? $headers['Webhook-Timestamp'] ?? null;
        $webhookSignature = $headers['webhook-signature'] ?? $headers['Webhook-Signature'] ?? null;

        if ($webhookId === null || $webhookTimestamp === null || $webhookSignature === null) {
            return false;
        }

        $timestamp = (int) $webhookTimestamp;
        $currentTimestamp = time();

        if (abs($currentTimestamp - $timestamp) > $tolerance) {
            return false;
        }

        $signedContent = "{$webhookId}.{$webhookTimestamp}.{$rawBody}";

        $secretParts = explode('_', $secret, 2);
        $secretBytes = base64_decode($secretParts[1] ?? '', true);

        if ($secretBytes === false) {
            return false;
        }

        $expectedSignature = base64_encode(
            hash_hmac('sha256', $signedContent, $secretBytes, true)
        );

        $signatures = array_map(
            fn (string $sig): string => self::extractSignature($sig),
            explode(' ', $webhookSignature)
        );

        foreach ($signatures as $signature) {
            if (hash_equals($expectedSignature, $signature)) {
                return true;
            }
        }

        return false;
    }

    protected static function extractSignature(string $signature): string
    {
        $parts = explode(',', $signature, 2);

        return count($parts) > 1 ? $parts[1] : $parts[0];
    }
}
