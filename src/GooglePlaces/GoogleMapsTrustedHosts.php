<?php

declare(strict_types=1);

namespace AIArmada\Addressing\GooglePlaces;

final class GoogleMapsTrustedHosts
{
    /**
     * Google Maps base domains the package follows. Only exact domains and
     * their www/maps subdomains are trusted; unsupported regional domains
     * fail closed instead of being resolved remotely.
     *
     * @var list<string>
     */
    private const array MAPS_DOMAINS = [
        'google.com',
        'google.ad',
        'google.ae',
        'google.com.ar',
        'google.at',
        'google.com.au',
        'google.be',
        'google.com.bd',
        'google.bg',
        'google.com.bo',
        'google.com.br',
        'google.ca',
        'google.ch',
        'google.com.cl',
        'google.com.co',
        'google.com.cr',
        'google.cz',
        'google.de',
        'google.dk',
        'google.com.do',
        'google.com.ec',
        'google.com.eg',
        'google.es',
        'google.fi',
        'google.fr',
        'google.com.gt',
        'google.com.hk',
        'google.com.hn',
        'google.hr',
        'google.hu',
        'google.co.id',
        'google.ie',
        'google.co.il',
        'google.co.in',
        'google.it',
        'google.co.jp',
        'google.co.ke',
        'google.co.kr',
        'google.com.mx',
        'google.com.my',
        'google.com.ng',
        'google.com.ni',
        'google.nl',
        'google.no',
        'google.co.nz',
        'google.com.pa',
        'google.com.pe',
        'google.com.ph',
        'google.com.pk',
        'google.pl',
        'google.com.pr',
        'google.pt',
        'google.com.py',
        'google.ro',
        'google.rs',
        'google.ru',
        'google.com.sa',
        'google.se',
        'google.com.sg',
        'google.si',
        'google.sk',
        'google.co.th',
        'google.com.tr',
        'google.com.tw',
        'google.com.ua',
        'google.co.uk',
        'google.com.uy',
        'google.co.uz',
        'google.co.ve',
        'google.com.vn',
        'google.co.za',
    ];

    /** @var list<string> */
    private const array SHORTENER_HOSTS = ['maps.app.goo.gl', 'goo.gl', 'g.co'];

    private const string CONSENT_HOST = 'consent.google.com';

    public static function isMapsHost(string $host): bool
    {
        $host = self::normalizeHost($host);

        foreach (self::MAPS_DOMAINS as $domain) {
            if ($host === $domain || $host === 'www.' . $domain || $host === 'maps.' . $domain) {
                return true;
            }
        }

        return false;
    }

    public static function isShortenerHost(string $host): bool
    {
        return in_array(self::normalizeHost($host), self::SHORTENER_HOSTS, true);
    }

    public static function isConsentHost(string $host): bool
    {
        return self::normalizeHost($host) === self::CONSENT_HOST;
    }

    public static function isSupportedHost(string $host): bool
    {
        return self::isMapsHost($host)
            || self::isShortenerHost($host)
            || self::isConsentHost($host);
    }

    public static function isSupportedUrl(string $url): bool
    {
        $host = parse_url($url, PHP_URL_HOST);

        return is_string($host) && self::isSupportedHost($host);
    }

    private static function normalizeHost(string $host): string
    {
        return mb_strtolower(mb_rtrim(mb_trim($host), '.'));
    }
}
