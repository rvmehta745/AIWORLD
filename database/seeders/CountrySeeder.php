<?php

namespace Database\Seeders;

use App\Models\Country;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CountrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $truncateSql = "TRUNCATE table countries";
        DB::connection()->getPdo()->exec($truncateSql);
        $countries = [
            [
                "id" => 1,
                "name" => "United States",
                "iso3" => "USA",
                "iso2" => "US",
                "numeric_code" => "840",
                "phone_code" => "1",
                "capital" => "Washington",
                "currency" => "USD",
                "currency_name" => "United States dollar",
                "currency_symbol" => "$",
                "created_at" => now(),
                "updated_at" => now(),
        ]
        ];

        foreach ($countries as $country) {
            Country::create($country);
        }
    }
}