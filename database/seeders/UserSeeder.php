<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $itDept = Department::where('name', 'IT отдел')->first();
        $hrDept = Department::where('name', 'HR отдел')->first();
        $financeDept = Department::where('name', 'Бухгалтерия')->first();
        $legalDept = Department::where('name', 'Юридический отдел')->first();
        $managementDept = Department::where('name', 'Управление')->first();

        $users = [
            [
                'name' => 'Админ Системы',
                'email' => 'admin@docflow.com',
                'password' => Hash::make('password'),
                'phone' => '+7 (999) 123-45-67',
                'position' => 'Системный администратор',
                'department_id' => $itDept?->id,
            ],
            [
                'name' => 'Руководитель Отдела',
                'email' => 'head@docflow.com',
                'password' => Hash::make('password'),
                'phone' => '+7 (999) 234-56-78',
                'position' => 'Директор',
                'department_id' => $managementDept?->id,
            ],
            [
                'name' => 'Главный Бухгалтер',
                'email' => 'accountant@docflow.com',
                'password' => Hash::make('password'),
                'phone' => '+7 (999) 345-67-89',
                'position' => 'Главный бухгалтер',
                'department_id' => $financeDept?->id,
            ],
            [
                'name' => 'Юрисконсульт',
                'email' => 'lawyer@docflow.com',
                'password' => Hash::make('password'),
                'phone' => '+7 (999) 456-78-90',
                'position' => 'Юрисконсульт',
                'department_id' => $legalDept?->id,
            ],
            [
                'name' => 'Сотрудник IT',
                'email' => 'employee@docflow.com',
                'password' => Hash::make('password'),
                'phone' => '+7 (999) 567-89-01',
                'position' => 'Разработчик',
                'department_id' => $itDept?->id,
            ],
        ];

        foreach ($users as $user) {
            User::create($user);
        }

        // Назначаем руководителей отделов
        if ($managementDept) {
            $head = User::where('email', 'head@docflow.com')->first();
            $managementDept->update(['head_id' => $head?->id]);
        }

        if ($itDept) {
            $admin = User::where('email', 'admin@docflow.com')->first();
            $itDept->update(['head_id' => $admin?->id]);
        }

        if ($financeDept) {
            $accountant = User::where('email', 'accountant@docflow.com')->first();
            $financeDept->update(['head_id' => $accountant?->id]);
        }

        if ($legalDept) {
            $lawyer = User::where('email', 'lawyer@docflow.com')->first();
            $legalDept->update(['head_id' => $lawyer?->id]);
        }
    }
}
