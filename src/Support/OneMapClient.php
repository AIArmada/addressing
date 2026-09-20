<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Support;

use AIArmada\Addressing\Exceptions\OneMapException;
use Carbon\CarbonImmutable;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/**
 * Minimal client for SLA's OneMap API (search + token auth).
 *
 * The access token is cached in the Laravel cache until shortly before its
 * reported expiry. No token state is held in memory, keeping this safe for
 * long-lived workers.
 */
class OneMapClient
{
    private const string TOKEN_CACHE_KEY = 'aiarmada.addressing.onemap.token';

    private const int TOKEN_EXPIRY_BUFFER_SECONDS = 3600;

    private const int MAX_RETRY_AFTER_SECONDS = 60;

    /**
     * Search OneMap for an address or postcode.
     *
     * @return list<array<string, mixed>>
     *
     * @throws OneMapException
     */
    public function search(string $query): array
    {
        $query = mb_trim($query);

        if ($query === '') {
            return [];
        }

        $response = $this->sendSearch($query);

        if ($response->status() === 401) {
            $this->forgetToken();
            $response = $this->sendSearch($query);
        }

        if ($response->status() === 401) {
            throw new OneMapException('OneMap authentication failed. Check ONEMAP_EMAIL and ONEMAP_PASSWORD.');
        }

        if ($response->failed()) {
            throw new OneMapException(sprintf(
                'OneMap search failed with status %d.',
                $response->status(),
            ));
        }

        $results = $response->json('results', []);

        if (! is_array($results)) {
            return [];
        }

        return array_values(array_filter(
            $results,
            static fn (mixed $row): bool => is_array($row),
        ));
    }

    /**
     * @throws OneMapException
     */
    private function sendSearch(string $query): Response
    {
        $token = $this->token();
        $retries = max(0, (int) config('addressing.onemap.retries', 2));

        $response = null;

        for ($attempt = 0; $attempt <= $retries; $attempt++) {
            try {
                $response = $this->http()
                    ->withToken($token)
                    ->get($this->baseUrl() . '/common/elastic/search', [
                        'searchVal' => $query,
                        'returnGeom' => 'Y',
                        'getAddrDetails' => 'Y',
                        'pageNum' => 1,
                    ]);
            } catch (ConnectionException $exception) {
                if ($attempt >= $retries) {
                    throw new OneMapException('OneMap search failed: ' . $exception->getMessage(), previous: $exception);
                }

                $this->sleep($attempt, null);

                continue;
            }

            if ($response->status() === 401) {
                return $response;
            }

            if (($response->status() === 429 || $response->serverError()) && $attempt < $retries) {
                $this->sleep($attempt, $response->header('Retry-After'));

                continue;
            }

            return $response;
        }

        throw new OneMapException('OneMap search failed: no response was received.');
    }

    /**
     * @throws OneMapException
     */
    private function token(): string
    {
        $cached = Cache::get(self::TOKEN_CACHE_KEY);

        if (is_string($cached) && $cached !== '') {
            return $cached;
        }

        $email = config('addressing.onemap.email');
        $password = config('addressing.onemap.password');

        if (! is_string($email) || $email === '' || ! is_string($password) || $password === '') {
            throw new OneMapException('OneMap credentials are not configured. Set ONEMAP_EMAIL and ONEMAP_PASSWORD.');
        }

        try {
            $response = $this->http()->post($this->baseUrl() . '/auth/post/getToken', [
                'email' => $email,
                'password' => $password,
            ]);
        } catch (ConnectionException $exception) {
            throw new OneMapException('OneMap authentication failed: ' . $exception->getMessage(), previous: $exception);
        }

        if ($response->failed()) {
            throw new OneMapException(sprintf(
                'OneMap authentication failed with status %d.',
                $response->status(),
            ));
        }

        $token = $response->json('access_token');

        if (! is_string($token) || $token === '') {
            throw new OneMapException('OneMap authentication failed: no access token was returned.');
        }

        Cache::put(self::TOKEN_CACHE_KEY, $token, $this->tokenTtl($response->json('expiry_timestamp')));

        return $token;
    }

    private function forgetToken(): void
    {
        Cache::forget(self::TOKEN_CACHE_KEY);
    }

    private function tokenTtl(mixed $expiryTimestamp): CarbonImmutable
    {
        $expiresAt = is_numeric($expiryTimestamp)
            ? CarbonImmutable::createFromTimestamp((int) $expiryTimestamp)
            : CarbonImmutable::now()->addDays(3);

        $cachedUntil = $expiresAt->subSeconds(self::TOKEN_EXPIRY_BUFFER_SECONDS);
        $minimum = CarbonImmutable::now()->addMinutes(5);

        return $cachedUntil->greaterThan($minimum) ? $cachedUntil : $minimum;
    }

    private function sleep(int $attempt, ?string $retryAfter): void
    {
        usleep((int) (self::retryDelaySeconds($attempt, $retryAfter) * 1000000));
    }

    public static function retryDelaySeconds(int $attempt, ?string $retryAfter): float
    {
        if ($retryAfter !== null && is_numeric(mb_trim($retryAfter))) {
            $seconds = max(0, (int) mb_trim($retryAfter));

            if ($seconds > 0) {
                return (float) min($seconds, self::MAX_RETRY_AFTER_SECONDS);
            }
        }

        return 0.2 * (2 ** $attempt);
    }

    private function baseUrl(): string
    {
        $baseUrl = config('addressing.onemap.base_url', 'https://www.onemap.gov.sg/api');

        return mb_rtrim((string) $baseUrl, '/');
    }

    private function http(): PendingRequest
    {
        return Http::acceptJson()
            ->timeout((int) config('addressing.onemap.timeout', 10));
    }
}
