<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Drivers\GoogleAddressReference;

use AIArmada\Addressing\Contracts\GoogleAddressReferenceSource;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;
use JsonException;
use RuntimeException;

final readonly class GoogleAddressReferenceClient implements GoogleAddressReferenceSource
{
    private const string ENDPOINT = 'https://chromium-i18n.appspot.com/ssl-address/data/';

    public function fetch(string $code): array
    {
        if (! preg_match('/^[A-Z]{2}$/D', $code)) {
            throw new InvalidArgumentException('Country code must contain two ASCII letters.');
        }

        $response = Http::acceptJson()->withOptions(['allow_redirects' => false])->connectTimeout(5)->timeout(15)->get(self::ENDPOINT . $code);

        if ($response->status() === 404) {
            return [];
        }

        if (! $response->successful()) {
            throw new RuntimeException("Could not fetch Google address data for [{$code}].");
        }

        try {
            $data = json_decode($response->body(), true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new RuntimeException("Google address data for [{$code}] is not valid JSON.", previous: $exception);
        }

        if (! is_array($data)) {
            throw new RuntimeException("Google address data for [{$code}] has an unexpected shape.");
        }

        return $data;
    }
}
