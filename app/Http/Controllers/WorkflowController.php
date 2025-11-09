<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Services\WorkflowService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WorkflowController extends Controller
{
    public function __construct(private WorkflowService $workflowService)
    {
    }

    /**
     * Execute workflow transition
     */
    public function transition(Request $request, Document $document)
    {
        $request->validate([
            'transition' => 'required|string',
            'comment' => 'nullable|string|max:1000',
        ]);

        $transitionName = $request->input('transition');
        $comment = $request->input('comment');
        
        // Проверяем права доступа
        if (!$this->canUserAccessDocument($document)) {
            abort(403, 'У вас нет доступа к этому документу.');
        }

        $success = $this->workflowService->transition($document, $transitionName, Auth::user());

        if ($success) {
            // Добавляем комментарий если есть
            if ($comment) {
                activity()
                    ->performedOn($document)
                    ->causedBy(Auth::user())
                    ->withProperties(['comment' => $comment])
                    ->log('Comment added during transition');
            }

            return redirect()->route('documents.show', $document)
                ->with('success', 'Переход выполнен успешно.');
        }

        return redirect()->back()
            ->with('error', 'Ошибка при выполнении перехода.');
    }

    /**
     * Start workflow for document
     */
    public function start(Document $document)
    {
        // Проверяем права доступа
        if (!$this->canUserAccessDocument($document)) {
            abort(403, 'У вас нет доступа к этому документу.');
        }

        $success = $this->workflowService->startWorkflow($document);

        if ($success) {
            return redirect()->route('documents.show', $document)
                ->with('success', 'Workflow запущен.');
        }

        return redirect()->back()
            ->with('error', 'Ошибка при запуске workflow.');
    }

    /**
     * Get workflow history for document
     */
    public function history(Document $document)
    {
        // Проверяем права доступа
        if (!$this->canUserAccessDocument($document)) {
            abort(403, 'У вас нет доступа к этому документу.');
        }

        $history = $this->workflowService->getWorkflowHistory($document);
        
        return view('documents.workflow-history', compact('document', 'history'));
    }

    /**
     * Check if user can access document
     */
    private function canUserAccessDocument(Document $document): bool
    {
        $user = Auth::user();
        
        // Админ может все
        if ($user->can('system.admin')) {
            return true;
        }

        // Автор документа может
        if ($document->author_id === $user->id) {
            return true;
        }

        // Пользователи того же отдела могут
        if ($document->department_id === $user->department_id) {
            return true;
        }

        return false;
    }
}
