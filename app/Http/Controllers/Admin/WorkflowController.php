<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Workflow;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class WorkflowController extends Controller
{
    /**
     * Display a listing of workflows
     */
    public function index()
    {
        $workflows = Workflow::with(['category', 'documents'])
            ->withCount('documents')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('admin.workflows.index', compact('workflows'));
    }

    /**
     * Show the form for creating a new workflow
     */
    public function create()
    {
        $categories = Category::orderBy('name')->get();
        
        return view('admin.workflows.create', compact('categories'));
    }

    /**
     * Store a newly created workflow
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:workflows,name',
            'description' => 'nullable|string',
            'category_id' => 'nullable|exists:categories,id',
            'initial_state' => 'required|string|max:50',
            'states' => 'required|array|min:2',
            'states.*' => 'required|string|max:50|distinct',
            'transitions' => 'required|array|min:1',
            'transitions.*.name' => 'required|string|max:100',
            'transitions.*.from' => 'required|array|min:1',
            'transitions.*.from.*' => 'required|string',
            'transitions.*.to' => 'required|string',
            'transitions.*.permissions' => 'nullable|array',
            'transitions.*.permissions.*' => 'string',
            'is_active' => 'boolean',
        ]);

        // Проверяем что initial_state есть в списке states
        if (!in_array($validated['initial_state'], $validated['states'])) {
            return back()->withErrors(['initial_state' => 'Начальное состояние должно быть в списке состояний.']);
        }

        // Проверяем что все переходы ссылаются на существующие состояния
        $allStates = $validated['states'];
        foreach ($validated['transitions'] as $transition) {
            foreach ($transition['from'] as $fromState) {
                if (!in_array($fromState, $allStates)) {
                    return back()->withErrors(['transitions' => "Состояние '{$fromState}' не найдено в списке состояний."]);
                }
            }
            if (!in_array($transition['to'], $allStates)) {
                return back()->withErrors(['transitions' => "Состояние '{$transition['to']}' не найдено в списке состояний."]);
            }
        }

        $workflow = Workflow::create($validated);

        activity()
            ->performedOn($workflow)
            ->causedBy(auth()->user())
            ->log('Workflow created');

        return redirect()->route('admin.workflows.index')
            ->with('success', 'Рабочий процесс успешно создан.');
    }

    /**
     * Display the specified workflow
     */
    public function show(Workflow $workflow)
    {
        $workflow->load(['category', 'documents.author', 'documents.category']);
        
        // Статистика по документам в разных состояниях
        $documentStats = $workflow->documents()
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        return view('admin.workflows.show', compact('workflow', 'documentStats'));
    }

    /**
     * Show the form for editing the workflow
     */
    public function edit(Workflow $workflow)
    {
        $categories = Category::orderBy('name')->get();
        
        return view('admin.workflows.edit', compact('workflow', 'categories'));
    }

    /**
     * Update the specified workflow
     */
    public function update(Request $request, Workflow $workflow)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('workflows')->ignore($workflow->id)],
            'description' => 'nullable|string',
            'category_id' => 'nullable|exists:categories,id',
            'initial_state' => 'required|string|max:50',
            'states' => 'required|array|min:2',
            'states.*' => 'required|string|max:50|distinct',
            'transitions' => 'required|array|min:1',
            'transitions.*.name' => 'required|string|max:100',
            'transitions.*.from' => 'required|array|min:1',
            'transitions.*.from.*' => 'required|string',
            'transitions.*.to' => 'required|string',
            'transitions.*.permissions' => 'nullable|array',
            'transitions.*.permissions.*' => 'string',
            'is_active' => 'boolean',
        ]);

        // Проверяем что initial_state есть в списке states
        if (!in_array($validated['initial_state'], $validated['states'])) {
            return back()->withErrors(['initial_state' => 'Начальное состояние должно быть в списке состояний.']);
        }

        // Проверяем что все переходы ссылаются на существующие состояния
        $allStates = $validated['states'];
        foreach ($validated['transitions'] as $transition) {
            foreach ($transition['from'] as $fromState) {
                if (!in_array($fromState, $allStates)) {
                    return back()->withErrors(['transitions' => "Состояние '{$fromState}' не найдено в списке состояний."]);
                }
            }
            if (!in_array($transition['to'], $allStates)) {
                return back()->withErrors(['transitions' => "Состояние '{$transition['to']}' не найдено в списке состояний."]);
            }
        }

        $workflow->update($validated);

        activity()
            ->performedOn($workflow)
            ->causedBy(auth()->user())
            ->log('Workflow updated');

        return redirect()->route('admin.workflows.index')
            ->with('success', 'Рабочий процесс успешно обновлен.');
    }

    /**
     * Remove the specified workflow
     */
    public function destroy(Workflow $workflow)
    {
        // Проверяем что workflow не используется в документах
        if ($workflow->documents()->exists()) {
            return back()->withErrors(['delete' => 'Невозможно удалить рабочий процесс, который используется в документах.']);
        }

        activity()
            ->performedOn($workflow)
            ->causedBy(auth()->user())
            ->log('Workflow deleted');

        $workflow->delete();

        return redirect()->route('admin.workflows.index')
            ->with('success', 'Рабочий процесс успешно удален.');
    }

    /**
     * Toggle workflow active status
     */
    public function toggleActive(Workflow $workflow)
    {
        $workflow->update(['is_active' => !$workflow->is_active]);

        activity()
            ->performedOn($workflow)
            ->causedBy(auth()->user())
            ->withProperties(['is_active' => $workflow->is_active])
            ->log('Workflow status changed');

        $status = $workflow->is_active ? 'активирован' : 'деактивирован';
        
        return back()->with('success', "Рабочий процесс {$status}.");
    }
}