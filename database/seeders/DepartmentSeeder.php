<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $departments = [
            [
                'name' => 'IT отдел',
                'description' => 'Отдел информационных технологий',
            ],
            [
                'name' => 'Бухгалтерия',
                'description' => 'Финансовый отдел',
            ],
            [
                'name' => 'Юридический отдел',
                'description' => 'Правовое обеспечение деятельности',
            ],
            [
                'name' => 'Управление',
                'description' => 'Административное управление',
            ],
            [
                'name' => 'HR отдел',
                'description' => 'Управление персоналом',
            ],
        ];

        foreach ($departments as $department) {
            Department::create($department);
        }
    }
}
