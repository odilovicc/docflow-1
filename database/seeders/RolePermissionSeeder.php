<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Создаём разрешения
        $permissions = [
            'document.create',
            'document.view',
            'document.edit',
            'document.delete',
            'document.approve',
            'document.execute',
            'workflow.manage',
            'department.manage',
            'user.manage',
            'system.admin',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Создаём роли и назначаем разрешения
        $adminRole = Role::create(['name' => 'Admin']);
        $adminRole->givePermissionTo(Permission::all());

        $headRole = Role::create(['name' => 'DepartmentHead']);
        $headRole->givePermissionTo([
            'document.create',
            'document.view',
            'document.edit',
            'document.approve',
            'document.execute',
        ]);

        $accountantRole = Role::create(['name' => 'Accountant']);
        $accountantRole->givePermissionTo([
            'document.create',
            'document.view',
            'document.edit',
            'document.approve',
        ]);

        $lawyerRole = Role::create(['name' => 'Lawyer']);
        $lawyerRole->givePermissionTo([
            'document.create',
            'document.view',
            'document.edit',
            'document.approve',
        ]);

        $employeeRole = Role::create(['name' => 'Employee']);
        $employeeRole->givePermissionTo([
            'document.create',
            'document.view',
        ]);

        // Назначаем роли пользователям
        $admin = User::where('email', 'admin@docflow.com')->first();
        $admin?->assignRole('Admin');

        $head = User::where('email', 'head@docflow.com')->first();
        $head?->assignRole('DepartmentHead');

        $accountant = User::where('email', 'accountant@docflow.com')->first();
        $accountant?->assignRole('Accountant');

        $lawyer = User::where('email', 'lawyer@docflow.com')->first();
        $lawyer?->assignRole('Lawyer');

        $employee = User::where('email', 'employee@docflow.com')->first();
        $employee?->assignRole('Employee');
    }
}
