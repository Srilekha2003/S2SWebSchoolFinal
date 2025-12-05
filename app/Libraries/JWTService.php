<?php
// app/Libraries/JWTService.php
namespace App\Libraries;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\SignatureInvalidException;
use Exception;

class JWTService
{
    protected string $secret;
    protected string $algo;

    public function __construct()
    {
        $this->secret = env('JWT_SECRET') ?: bin2hex(random_bytes(32)); // fallback secure random secret
        $this->algo   = env('JWT_ALGO') ?: 'HS256';
    }

    /* ---------------------------------------------------------
       ACCESS TOKEN  (short-lived, 1 hour default)
    --------------------------------------------------------- */
    public function createAccessToken(array $payload, int $ttlSeconds = 3600): string
    {
        return $this->encodeToken($payload, $ttlSeconds);
    }

    /* ---------------------------------------------------------
       REFRESH TOKEN (long-lived, 30 days default)
    --------------------------------------------------------- */
    public function createRefreshToken(array $payload, int $ttlSeconds = 2592000): string
    {
        return $this->encodeToken($payload, $ttlSeconds);
    }

    /**
     * Create a JWT token with standard claims.
     *
     * @param array $payload Custom claims (user id, role, etc.)
     * @param int $ttlSeconds Time to live in seconds
     * @return string
     */
    private function encodeToken(array $payload, int $ttlSeconds): string
    {
        $now = time();
        $claims = array_merge([
            'iat' => $now,                // issued at
            'nbf' => $now,                // not before
            'exp' => $now + $ttlSeconds,  // expiration
        ], $payload);

        return JWT::encode($claims, $this->secret, $this->algo);
    }

    /**
     * Decode JWT token.
     *
     * @param string $token
     * @return object|null Returns payload object or null if invalid
     */
    public function decodeToken(string $token): ?object
    {
        try {
            return JWT::decode($token, new Key($this->secret, $this->algo));
        } catch (ExpiredException $e) {
            log_message('error', 'JWT Expired: ' . $e->getMessage());
        } catch (SignatureInvalidException $e) {
            log_message('error', 'JWT Signature Invalid: ' . $e->getMessage());
        } catch (Exception $e) {
            log_message('error', 'JWT Error: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Verify if token is valid and not expired
     *
     * @param string $token
     * @return bool
     */
    public function isValid(string $token): bool
    {
        return $this->decodeToken($token) !== null;
    }
}
