<?php

declare(strict_types=1);

namespace AIArmada\Addressing\Database\Seeders;

use AIArmada\Addressing\Models\AddressCountry;
use AIArmada\Addressing\Models\City;
use AIArmada\Addressing\Models\State;
use Illuminate\Database\Seeder;

class MalaysiaGeographySeeder extends Seeder
{
    public function run(): void
    {
        $malaysia = AddressCountry::firstOrCreate(
            ['iso2' => 'MY'],
            [
                'iso3' => 'MYS',
                'numeric_code' => '458',
                'name' => 'Malaysia',
                'official_name' => 'Malaysia',
                'native_name' => 'Malaysia',
                'emoji' => '🇲🇾',
                'phone_code' => '60',
                'currency_codes' => ['MYR'],
                'default_currency_code' => 'MYR',
                'language_codes' => ['ms', 'en'],
                'region' => 'Asia',
                'subregion' => 'South-Eastern Asia',
            ],
        );

        $statesData = $this->stateDefinitions();

        foreach ($statesData as $s) {
            $state = State::firstOrCreate(
                ['country_id' => $malaysia->id, 'code' => $s['code']],
                [
                    'name' => $s['name'],
                    'label' => $s['label'],
                    'metadata' => $s['metadata'] ?? null,
                ],
            );

            $cities = $this->cityDefinitions()[$s['code']] ?? [];
            foreach ($cities as $cityData) {
                City::firstOrCreate(
                    ['state_id' => $state->id, 'name' => $cityData['name']],
                    [
                        'postcode' => $cityData['postcode'] ?? null,
                        'label' => $cityData['label'] ?? null,
                        'country_id' => $malaysia->id,
                    ],
                );
            }
        }
    }

    /**
     * @return list<array{name: string, code: string, label: string, metadata?: array}>
     */
    private function stateDefinitions(): array
    {
        return [
            ['name' => 'Johor', 'code' => 'MY-01', 'label' => 'Johor'],
            ['name' => 'Kedah', 'code' => 'MY-02', 'label' => 'Kedah'],
            ['name' => 'Kelantan', 'code' => 'MY-03', 'label' => 'Kelantan'],
            ['name' => 'Melaka', 'code' => 'MY-04', 'label' => 'Melaka'],
            ['name' => 'Negeri Sembilan', 'code' => 'MY-05', 'label' => 'Negeri Sembilan'],
            ['name' => 'Pahang', 'code' => 'MY-06', 'label' => 'Pahang'],
            ['name' => 'Perak', 'code' => 'MY-08', 'label' => 'Perak'],
            ['name' => 'Perlis', 'code' => 'MY-09', 'label' => 'Perlis'],
            ['name' => 'Pulau Pinang', 'code' => 'MY-07', 'label' => 'Pulau Pinang'],
            ['name' => 'Sabah', 'code' => 'MY-12', 'label' => 'Sabah'],
            ['name' => 'Sarawak', 'code' => 'MY-13', 'label' => 'Sarawak'],
            ['name' => 'Selangor', 'code' => 'MY-10', 'label' => 'Selangor'],
            ['name' => 'Terengganu', 'code' => 'MY-11', 'label' => 'Terengganu'],
            ['name' => 'WP Kuala Lumpur', 'code' => 'MY-14', 'label' => 'Wilayah Persekutuan Kuala Lumpur'],
            ['name' => 'WP Labuan', 'code' => 'MY-15', 'label' => 'Wilayah Persekutuan Labuan'],
            ['name' => 'WP Putrajaya', 'code' => 'MY-16', 'label' => 'Wilayah Persekutuan Putrajaya'],
        ];
    }

    /**
     * @return array<string, list<array{name: string, postcode?: string, label?: string}>>
     */
    private function cityDefinitions(): array
    {
        return [
            'MY-01' => [
                ['name' => 'Johor Bahru', 'postcode' => '80000'],
                ['name' => 'Batu Pahat', 'postcode' => '83000'],
                ['name' => 'Muar', 'postcode' => '84000'],
                ['name' => 'Kluang', 'postcode' => '86000'],
                ['name' => 'Segamat', 'postcode' => '85000'],
                ['name' => 'Pontian', 'postcode' => '82000'],
                ['name' => 'Kota Tinggi', 'postcode' => '81900'],
                ['name' => 'Iskandar Puteri', 'postcode' => '79100'],
                ['name' => 'Pasir Gudang', 'postcode' => '81700'],
            ],
            'MY-02' => [
                ['name' => 'Alor Setar', 'postcode' => '05000'],
                ['name' => 'Sungai Petani', 'postcode' => '08000'],
                ['name' => 'Kulim', 'postcode' => '09000'],
                ['name' => 'Langkawi', 'postcode' => '07000'],
                ['name' => 'Jitra', 'postcode' => '06000'],
            ],
            'MY-03' => [
                ['name' => 'Kota Bharu', 'postcode' => '15000'],
                ['name' => 'Pasir Mas', 'postcode' => '17000'],
                ['name' => 'Tumpat', 'postcode' => '16200'],
                ['name' => 'Tanah Merah', 'postcode' => '17500'],
            ],
            'MY-04' => [
                ['name' => 'Bandaraya Melaka', 'postcode' => '75000'],
                ['name' => 'Alor Gajah', 'postcode' => '78000'],
                ['name' => 'Jasin', 'postcode' => '77000'],
            ],
            'MY-05' => [
                ['name' => 'Seremban', 'postcode' => '70000'],
                ['name' => 'Port Dickson', 'postcode' => '71000'],
                ['name' => 'Nilai', 'postcode' => '71800'],
                ['name' => 'Tampin', 'postcode' => '73000'],
            ],
            'MY-06' => [
                ['name' => 'Kuantan', 'postcode' => '25000'],
                ['name' => 'Temerloh', 'postcode' => '28000'],
                ['name' => 'Bentong', 'postcode' => '28700'],
                ['name' => 'Raub', 'postcode' => '27600'],
            ],
            'MY-07' => [
                ['name' => 'George Town', 'postcode' => '10000'],
                ['name' => 'Butterworth', 'postcode' => '12000'],
                ['name' => 'Bukit Mertajam', 'postcode' => '14000'],
                ['name' => 'Bayan Lepas', 'postcode' => '11900'],
                ['name' => 'Balik Pulau', 'postcode' => '11000'],
            ],
            'MY-08' => [
                ['name' => 'Ipoh', 'postcode' => '30000'],
                ['name' => 'Taiping', 'postcode' => '34000'],
                ['name' => 'Teluk Intan', 'postcode' => '36000'],
                ['name' => 'Manjung', 'postcode' => '32000'],
                ['name' => 'Kuala Kangsar', 'postcode' => '33000'],
            ],
            'MY-09' => [
                ['name' => 'Kangar', 'postcode' => '01000'],
                ['name' => 'Arau', 'postcode' => '02600'],
            ],
            'MY-10' => [
                ['name' => 'Shah Alam', 'postcode' => '40000'],
                ['name' => 'Petaling Jaya', 'postcode' => '46000'],
                ['name' => 'Subang Jaya', 'postcode' => '47500'],
                ['name' => 'Klang', 'postcode' => '41000'],
                ['name' => 'Kajang', 'postcode' => '43000'],
                ['name' => 'Ampang', 'postcode' => '68000'],
                ['name' => 'Selayang', 'postcode' => '68100'],
                ['name' => 'Rawang', 'postcode' => '48000'],
            ],
            'MY-11' => [
                ['name' => 'Kuala Terengganu', 'postcode' => '20000'],
                ['name' => 'Kemaman', 'postcode' => '24000'],
                ['name' => 'Dungun', 'postcode' => '23000'],
            ],
            'MY-12' => [
                ['name' => 'Kota Kinabalu', 'postcode' => '88000'],
                ['name' => 'Sandakan', 'postcode' => '90000'],
                ['name' => 'Tawau', 'postcode' => '91000'],
                ['name' => 'Lahad Datu', 'postcode' => '91100'],
            ],
            'MY-13' => [
                ['name' => 'Kuching', 'postcode' => '93000'],
                ['name' => 'Miri', 'postcode' => '98000'],
                ['name' => 'Sibu', 'postcode' => '96000'],
                ['name' => 'Bintulu', 'postcode' => '97000'],
            ],
            'MY-14' => [
                ['name' => 'Kuala Lumpur', 'postcode' => '50000'],
            ],
            'MY-15' => [
                ['name' => 'Labuan', 'postcode' => '87000'],
            ],
            'MY-16' => [
                ['name' => 'Putrajaya', 'postcode' => '62000'],
            ],
        ];
    }
}
