<?php

namespace App\Services;

use App\Models\Document;
use App\Models\User;
use App\Models\Workflow;
use App\Models\WorkflowStep;
use App\Models\Task;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\Facades\LogBatch;
use Spatie\Activitylog\Models\Activity;
use Symfony\Component\Workflow\Definition;
use Symfony\Component\Workflow\MarkingStore\MethodMarkingStore;
use Symfony\Component\Workflow\Transition;
use Symfony\Component\Workflow\Workflow as SymfonyWorkflow;

class WorkflowService
{
    /**
     * Start workflow for a document
     */
    public function startWorkflow(Document $document): bool
    {
        // Получаем workflow по категории документа или дефолтный
        $workflow = $this->getWorkflowForDocument($document);
        
        if (!$workflow) {
            return false;
        }

        $document->update([
            'workflow_id' => $workflow->id,
            'status' => $workflow->initial_state,
            'current_step' => $workflow->initial_state
        ]);

        // Логируем начало workflow
        activity()
            ->performedOn($document)
            ->causedBy(Auth::user())
            ->withProperties([
                'workflow_id' => $workflow->id,
                'workflow_name' => $workflow->name,
                'initial_state' => $workflow->initial_state
            ])
            ->log('Workflow started');

        return true;
    }

    /**
     * Execute transition for a document
     */
    public function transition(Document $document, string $transitionName, User $user): bool
    {
        $workflow = $document->workflow;
        
        if (!$workflow) {
            return false;
        }

        // Создаем Symfony Workflow
        $symfonyWorkflow = $this->buildSymfonyWorkflow($workflow);
        
        // Проверяем возможность перехода
        if (!$symfonyWorkflow->can($document, $transitionName)) {
            return false;
        }

        // Проверяем права пользователя
        if (!$this->canUserPerformTransition($user, $document, $transitionName)) {
            return false;
        }

        try {
            // Выполняем переход
            $symfonyWorkflow->apply($document, $transitionName);
            
            // Обновляем статус документа
            $newState = $document->status; // Symfony Workflow уже обновил через маркинг
            $document->update([
                'current_step' => $newState
            ]);

            // Логируем переход
            activity()
                ->performedOn($document)
                ->causedBy($user)
                ->withProperties([
                    'transition' => $transitionName,
                    'from_state' => $document->getOriginal('status'),
                    'to_state' => $newState,
                    'workflow_id' => $workflow->id
                ])
                ->log('Workflow transition executed');

            // Создаём задачу для следующего этапа
            $this->createTaskForNextStep($document, $newState, $user);

            return true;
        } catch (\Exception $e) {
            // Логируем ошибку
            activity()
                ->performedOn($document)
                ->causedBy($user)
                ->withProperties([
                    'transition' => $transitionName,
                    'error' => $e->getMessage()
                ])
                ->log('Workflow transition failed');

            return false;
        }
    }

    /**
     * Get available transitions for user and document
     */
    public function availableTransitions(User $user, Document $document): array
    {
        try {
            \Log::info("WorkflowService::availableTransitions called", [
                'user_id' => $user->id,
                'document_id' => $document->id,
                'document_status' => $document->status,
                'document_state' => $document->toArray(),
                'workflow_id' => $document->workflow_id
            ]);

            $workflow = $document->workflow;
            
            if (!$workflow) {
                \Log::warning("No workflow found for document", [
                    'document_id' => $document->id,
                    'workflow_id' => $document->workflow_id
                ]);
                return [];
            }

            \Log::info("Building Symfony workflow", [
                'workflow_id' => $workflow->id,
                'workflow_name' => $workflow->name,
                'workflow_states' => $workflow->states,
                'workflow_transitions' => $workflow->transitions
            ]);

            $symfonyWorkflow = $this->buildSymfonyWorkflow($workflow);
            
            \Log::info("Getting enabled transitions", [
                'document_status_method' => method_exists($document, 'getStatus') ? 'exists' : 'missing',
                'document_status_property' => property_exists($document, 'status') ? 'exists' : 'missing',
                'current_status' => $document->getStatus()
            ]);

            $enabledTransitions = $symfonyWorkflow->getEnabledTransitions($document);
        } catch (\Exception $e) {
            \Log::error("Error in WorkflowService::availableTransitions", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'document_id' => $document->id,
                'document_status' => $document->status,
                'document_state' => $document->toArray(),
                'user_id' => $user->id
            ]);
            
            return [];
        }
        
        $availableTransitions = [];
        
        foreach ($enabledTransitions as $transition) {
            if ($this->canUserPerformTransition($user, $document, $transition->getName())) {
                $availableTransitions[] = [
                    'name' => $transition->getName(),
                    'label' => $this->getTransitionLabel($transition->getName()),
                    'to_state' => $transition->getTos()[0] ?? null
                ];
            }
        }

        return $availableTransitions;
    }

    /**
     * Get workflow for document based on category
     */
    private function getWorkflowForDocument(Document $document): ?Workflow
    {
        // Ищем workflow для категории документа
        $workflow = Workflow::where('category_id', $document->category_id)
            ->where('is_active', true)
            ->first();

        // Если не найден, берем дефолтный workflow
        if (!$workflow) {
            $workflow = Workflow::whereNull('category_id')
                ->where('is_active', true)
                ->first();
        }

        return $workflow;
    }

    /**
     * Build Symfony Workflow from our workflow model
     */
    private function buildSymfonyWorkflow(Workflow $workflow): SymfonyWorkflow
    {
        $places = $workflow->states;
        $transitions = [];

        foreach ($workflow->transitions as $transitionData) {
            $transitions[] = new Transition(
                $transitionData['name'],
                $transitionData['from'],
                $transitionData['to']
            );
        }

        $definition = new Definition($places, $transitions);
        $markingStore = new MethodMarkingStore(true, 'status');

        return new SymfonyWorkflow($definition, $markingStore);
    }

    /**
     * Check if user can perform transition
     */
    private function canUserPerformTransition(User $user, Document $document, string $transitionName): bool
    {
        \Log::info("Checking if user can perform transition", [
            'user_id' => $user->id,
            'document_id' => $document->id,
            'transition_name' => $transitionName,
            'user_roles' => $user->getRoleNames()->toArray(),
            'user_permissions' => $user->getAllPermissions()->pluck('name')->toArray()
        ]);

        // Пока что разрешаем всем пользователям выполнять переходы
        // В будущем здесь можно добавить более сложную логику проверки прав
        
        // Проверяем основные права на документ
        if ($user->can('system.admin')) {
            \Log::info("User is admin - allowing transition");
            return true;
        }

        // Автор документа может выполнять переходы
        if ($document->author_id === $user->id) {
            \Log::info("User is document author - allowing transition");
            return true;
        }

        // Пользователи того же отдела могут выполнять переходы
        if ($document->department_id === $user->department_id) {
            \Log::info("User is in same department - allowing transition");
            return true;
        }

        \Log::info("User cannot perform transition - access denied");
        return false;
    }

    /**
     * Get human-readable transition label
     */
    private function getTransitionLabel(string $transitionName): string
    {
        $labels = [
            'submit' => 'Отправить на согласование',
            'approve' => 'Одобрить',
            'reject' => 'Отклонить',
            'complete' => 'Завершить',
            'return_to_draft' => 'Вернуть в черновик',
        ];

        return $labels[$transitionName] ?? ucfirst($transitionName);
    }

    /**
     * Get workflow history for document
     */
    public function getWorkflowHistory(Document $document): \Illuminate\Database\Eloquent\Collection
    {
        return Activity::forSubject($document)
            ->where('description', 'LIKE', '%workflow%')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Create task for next workflow step
     */
    private function createTaskForNextStep(Document $document, string $newState, User $assignedBy): void
    {
        $workflow = $document->workflow;
        $workflowStep = $workflow->steps()
            ->where('state', $newState)
            ->first();

        if (!$workflowStep || $newState === 'completed') {
            return; // Нет шага или документ завершён
        }

        // Определяем пользователей для назначения
        $assignees = $this->getAssigneesForStep($workflowStep, $document);

        foreach ($assignees as $assignee) {
            $dueDate = null;
            if ($workflowStep->sla_hours) {
                $dueDate = now()->addHours($workflowStep->sla_hours);
            }

            $task = Task::create([
                'document_id' => $document->id,
                'workflow_step_id' => $workflowStep->id,
                'assigned_to' => $assignee->id,
                'assigned_by' => $assignedBy->id,
                'title' => "Выполнить этап: {$workflowStep->name}",
                'description' => "Документ \"{$document->title}\" требует выполнения этапа \"{$workflowStep->name}\". " . 
                              ($workflowStep->description ?: 'Описание этапа не указано.'),
                'status' => 'pending',
                'due_date' => $dueDate,
            ]);

            // Отправляем уведомление о назначении задачи
            $assignee->notify(new \App\Notifications\TaskAssigned($task));
        }
    }

    /**
     * Get users assigned to workflow step
     */
    private function getAssigneesForStep(WorkflowStep $workflowStep, Document $document): \Illuminate\Database\Eloquent\Collection
    {
        $query = User::query();

        // Фильтруем по ролям, если указаны
        if ($workflowStep->required_roles) {
            $query->role($workflowStep->required_roles);
        }

        // Фильтруем по департаменту документа
        if ($document->department_id) {
            $query->where('department_id', $document->department_id);
        }

        // Если роли не указаны, назначаем руководителю департамента
        if (!$workflowStep->required_roles && $document->department_id) {
            $query->role(['DepartmentHead', 'Admin']);
        }

        return $query->get();
    }
}