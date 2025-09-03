<?php

namespace Database\Seeders;

use App\Models\PriceType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PriceTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $priceTypes = [
            [
                'product_type_id' => 1,
                'name' => 'Free',
                'status' => 'Active',
            ],
            [
                'product_type_id' => 1,
                'name' => 'Freemium',
                'status' => 'Active',
            ],
            [
                'product_type_id' => 1,
                'name' => 'Free Trial',
                'status' => 'Active',
            ],
            [
                'product_type_id' => 1,
                'name' => 'Paid',
                'status' => 'Active',
            ],
        ];

        foreach ($priceTypes as $type) {
            PriceType::create($type);
        }
    }
}
