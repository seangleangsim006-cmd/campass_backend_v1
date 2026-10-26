<?php

namespace App\Services;

use Illuminate\Support\Facades\{Cache, Http};
use Illuminate\Validation\ValidationException;
use Throwable;

use Firebase\JWT\{JWK, JWT};

class SocialTokenVerifier
{
    private const PROVIDERS = [
        'google' => [
            'jwks'   => 'https://www.googleapis.com/oauth2/v3/certs',
            'issuer' => ['https://accounts.google.com', 'accounts.google.com'],
        ],
        'apple' => [
            'jwks'   => 'https://appleid.apple.com/auth/keys',
            'issuer' => ['https://appleid.apple.com'],
        ],
    ];

    /**
     * Verifies the ID token's signature, expiry, issuer and audience.
     *
     * @return array{sub: string, email: ?string, email_verified: bool, name: ?string}
     */
    public function verify(string $provider, string $idToken, ?string $nonce = null): array
    {
        $config    = self::PROVIDERS[$provider] ?? null;
        $audiences = config("services.{$provider}.client_ids", []);

        if (! $config || empty($audiences)) {
            $this->fail();
        }

        try {
            JWT::$leeway = 60; // tolerate small clock differences
            $claims = JWT::decode($idToken, JWK::parseKeySet($this->keys($config['jwks']), 'RS256'));
        } catch (Throwable) {
            $this->fail();
        }

        if (
            ! in_array($claims->iss ?? null, $config['issuer'], true)
            || array_intersect((array) ($claims->aud ?? []), $audiences) === []
            || empty($claims->sub)
        ) {
            $this->fail();
        }

        // Apple puts the SHA-256 of the raw nonce in the token; Google may use the raw value
        if ($nonce !== null && ! in_array($claims->nonce ?? null, [$nonce, hash('sha256', $nonce)], true)) {
            $this->fail();
        }

        return [
            'sub'            => (string) $claims->sub,
            'email'          => $claims->email ?? null,
            'email_verified' => filter_var($claims->email_verified ?? false, FILTER_VALIDATE_BOOLEAN),
            'name'           => $claims->name ?? null,
        ];
    }

    /** The providers' public keys, cached for one hour. */
    private function keys(string $url): array
    {
        return Cache::remember('jwks:' . md5($url), 3600, fn () => Http::timeout(5)->get($url)->throw()->json());
    }

    private function fail(): never
    {
        throw ValidationException::withMessages([
            'id_token' => ['The social login token is invalid or expired.'],
        ]);
    }
}