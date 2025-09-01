<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'Makanan', 'is_active' => true],
            ['name' => 'Minuman', 'is_active' => true],
            ['name' => 'Alat Tulis', 'is_active' => true],
            ['name' => 'Atribut & Perlengkapan Lainnya', 'is_active' => true],
        ];

        foreach ($categories as $category) {
            Category::updateOrCreate(
                ['name' => $category['name']], // cek berdasarkan nama biar ga duplikat
                ['is_active' => $category['is_active']]
            );
        }
    }
}
