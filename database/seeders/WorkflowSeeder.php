<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Workflow;
use App\Models\WorkflowStep;
use Illuminate\Database\Seeder;

class WorkflowSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Дефолтный workflow для всех документов
        $defaultWorkflow = Workflow::create([
            'name' => 'Базовый workflow',
            'description' => 'Стандартный процесс согласования документов',
            'initial_state' => 'draft',
            'states' => ['draft', 'in_progress', 'approved', 'rejected', 'completed'],
            'transitions' => [
                [
                    'name' => 'submit',
                    'from' => ['draft'],
                    'to' => 'in_progress'
                ],
                [
                    'name' => 'approve', 
                    'from' => ['in_progress'],
                    'to' => 'approved'
                ],
                [
                    'name' => 'reject',
                    'from' => ['in_progress'],
                    'to' => 'rejected'
                ],
                [
                    'name' => 'complete',
                    'from' => ['approved'],
                    'to' => 'completed'
                ],
                [
                    'name' => 'return_to_draft',
                    'from' => ['rejected'],
                    'to' => 'draft'
                ]
            ],
            'category_id' => null, // Дефолтный для всех категорий
            'is_active' => true,
        ]);

        // Шаги для дефолтного workflow
        WorkflowStep::create([
            'workflow_id' => $defaultWorkflow->id,
            'name' => 'Черновик',
            'state' => 'draft',
            'order' => 1,
            'required_roles' => null,
            'required_permissions' => ['document.create'],
            'sla_days' => null,
            'auto_assign' => false,
            'description' => 'Создание и редактирование документа'
        ]);

        WorkflowStep::create([
            'workflow_id' => $defaultWorkflow->id,
            'name' => 'На согласовании',
            'state' => 'in_progress',
            'order' => 2,
            'required_roles' => ['DepartmentHead'],
            'required_permissions' => ['document.approve'],
            'sla_days' => 3,
            'auto_assign' => true,
            'description' => 'Согласование руководителем отдела'
        ]);

        WorkflowStep::create([
            'workflow_id' => $defaultWorkflow->id,
            'name' => 'Одобрен',
            'state' => 'approved',
            'order' => 3,
            'required_roles' => ['Admin', 'DepartmentHead'],
            'required_permissions' => ['document.execute'],
            'sla_days' => 7,
            'auto_assign' => false,
            'description' => 'Документ одобрен к исполнению'
        ]);

        // Workflow для финансовых документов
        $financeCategory = Category::where('name', 'Финансовые документы')->first();
        if ($financeCategory) {
            $financeWorkflow = Workflow::create([
                'name' => 'Финансовый workflow',
                'description' => 'Процесс согласования финансовых документов',
                'initial_state' => 'draft',
                'states' => ['draft', 'finance_review', 'legal_review', 'approved', 'rejected', 'completed'],
                'transitions' => [
                    [
                        'name' => 'submit_to_finance',
                        'from' => ['draft'],
                        'to' => 'finance_review'
                    ],
                    [
                        'name' => 'approve_finance',
                        'from' => ['finance_review'],
                        'to' => 'legal_review'
                    ],
                    [
                        'name' => 'reject_finance',
                        'from' => ['finance_review'],
                        'to' => 'rejected'
                    ],
                    [
                        'name' => 'approve_legal',
                        'from' => ['legal_review'],
                        'to' => 'approved'
                    ],
                    [
                        'name' => 'reject_legal',
                        'from' => ['legal_review'],
                        'to' => 'rejected'
                    ],
                    [
                        'name' => 'complete',
                        'from' => ['approved'],
                        'to' => 'completed'
                    ]
                ],
                'category_id' => $financeCategory->id,
                'is_active' => true,
            ]);

            WorkflowStep::create([
                'workflow_id' => $financeWorkflow->id,
                'name' => 'Проверка бухгалтерии',
                'state' => 'finance_review',
                'order' => 2,
                'required_roles' => ['Accountant'],
                'required_permissions' => ['document.approve'],
                'sla_days' => 2,
                'auto_assign' => true,
                'description' => 'Проверка финансовых данных'
            ]);

            WorkflowStep::create([
                'workflow_id' => $financeWorkflow->id,
                'name' => 'Юридическая проверка',
                'state' => 'legal_review',
                'order' => 3,
                'required_roles' => ['Lawyer'],
                'required_permissions' => ['document.approve'],
                'sla_days' => 2,
                'auto_assign' => true,
                'description' => 'Проверка юридических аспектов'
            ]);
        }
    }
}
