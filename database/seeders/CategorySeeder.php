<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Финансовые документы',
                'description' => 'Документы связанные с финансами и бухгалтерией',
                'color' => '#10B981',
            ],
            [
                'name' => 'Договоры',
                'description' => 'Договоры и соглашения',
                'color' => '#3B82F6',
            ],
            [
                'name' => 'Внутренние распоряжения',
                'description' => 'Приказы, распоряжения и внутренние документы',
                'color' => '#8B5CF6',
            ],
            [
                'name' => 'Отчёты',
                'description' => 'Различные виды отчётов',
                'color' => '#F59E0B',
            ],
            [
                'name' => 'Кадровые документы',
                'description' => 'Документы по управлению персоналом',
                'color' => '#EF4444',
            ],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}
