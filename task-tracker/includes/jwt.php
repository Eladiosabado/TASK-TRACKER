<?php
/**
 * includes/jwt.php
 *
 * Minimal, dependency-free JWT implementation supporting HS256.
 * This avoids requiring Composer/firebase-php-jwt so the project
 * runs on plain PHP + Apache/XAMPP with zero extra setup.
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/constants.php';

class Jwt
{
    /**
     * Base64Url encode.
     */
    private static function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * Base64Url decode.
     */
    private static function base64UrlDecode(string $data): string
    {
        $remainder = strlen($data) % 4;
        if ($remainder) {
            $data .= str_repeat('=', 4 - $remainder);
        }
        return base64_decode(strtr($data, '-_', '+/'));
    }

    /**
     * Create a signed JWT.
     *
     * @param array $claims Custom claims to embed (e.g. user_id, email)
     * @param int   $expiresInSeconds Token lifetime in seconds
     */
    public static function encode(array $claims, int $expiresInSeconds = JWT_EXPIRATION_SECONDS): string
    {
        $header = [
            'typ' => 'JWT',
            'alg' => JWT_ALGO,
        ];

        $now = time();

        $payload = array_merge($claims, [
            'iat' => $now,
            'exp' => $now + $expiresInSeconds,
        ]);

        $segments = [
            self::base64UrlEncode(json_encode($header)),
            self::base64UrlEncode(json_encode($payload)),
        ];

        $signingInput = implode('.', $segments);
        $signature    = hash_hmac('sha256', $signingInput, JWT_SECRET, true);
        $segments[]   = self::base64UrlEncode($signature);

        return implode('.', $segments);
    }

    /**
     * Decode and verify a JWT.
     *
     * @return array|null Decoded payload on success, null on failure.
     */
    public static function decode(string $token): ?array
    {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return null;
        }

        [$headerB64, $payloadB64, $signatureB64] = $parts;

        $header = json_decode(self::base64UrlDecode($headerB64), true);
        if (!is_array($header) || ($header['alg'] ?? '') !== JWT_ALGO) {
            return null;
        }

        $signingInput      = $headerB64 . '.' . $payloadB64;
        $expectedSignature = hash_hmac('sha256', $signingInput, JWT_SECRET, true);
        $actualSignature    = self::base64UrlDecode($signatureB64);

        if (!hash_equals($expectedSignature, $actualSignature)) {
            return null; // Invalid signature
        }

        $payload = json_decode(self::base64UrlDecode($payloadB64), true);
        if (!is_array($payload)) {
            return null;
        }

        if (!isset($payload['exp']) || time() >= (int) $payload['exp']) {
            return null; // Expired
        }

        return $payload;
    }
}
