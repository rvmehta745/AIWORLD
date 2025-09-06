<?php

namespace Database\Seeders;

use App\Models\ProductType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'name' => 'AI Tools',
                'tag_line' => 'Access the largest list of top-quality AI tools available on the web',
                'sort_order' => 1,
                'status' => 'Active',
            ],
            [
                'name' => 'AI GPTs',
                'tag_line' => 'Access the largest list of top-quality AI GPTs available on the web',
                'sort_order' => 2,
                'status' => 'Active',
            ],
            [
                'name' => 'AI Education',
                'tag_line' => 'Access the largest list of top-quality AI Education available on the web',
                'sort_order' => 3,
                'status' => 'Active',
            ],
            [
                'name' => 'AI Videos',
                'tag_line' => 'Access the largest list of top-quality AI Videos available on the web',
                'sort_order' => 4,
                'status' => 'Active',
            ],
            [
                'name' => 'AI Discord',
                'tag_line' => 'Access the largest list of top-quality AI Discord available on the web',
                'sort_order' => 5,
                'status' => 'Active',
            ],
            [
                'name' => 'AI News',
                'tag_line' => 'Access the largest list of top-quality AI News available on the web',
                'sort_order' => 6,
                'status' => 'Active',
            ],
            [
                'name' => 'AI Browser Extensions',
                'tag_line' => 'Access the largest list of top-quality AI Browser Extensions available on the web',
                'sort_order' => 7,
                'status' => 'Active',
            ],
            [
                'name' => 'AI MCP Servers',
                'tag_line' => 'Access the largest list of top-quality AI MCP Servers available on the web',
                'sort_order' => 8,
                'status' => 'Active',
            ],
            [
                'name' => 'AI Blogs',
                'tag_line' => 'Access the largest list of top-quality AI Blogs available on the web',
                'sort_order' => 9,
                'status' => 'Active',
            ],
            [
                'name' => 'AI Jobs',
                'tag_line' => 'Access the largest list of top-quality AI Jobs available on the web',
                'sort_order' => 10,
                'status' => 'Active',
            ],
            [
                'name' => 'AI Events',
                'tag_line' => 'Access the largest list of top-quality AI Events available on the web',
                'sort_order' => 11,
                'status' => 'Active',
            ],
            [
                'name' => 'AI Conferences',
                'tag_line' => 'Access the largest list of top-quality AI Conferences available on the web',
                'sort_order' => 12,
                'status' => 'Active',
            ],
        ];

        foreach ($data as $item) {
            ProductType::create($item);
        }
    }
}
