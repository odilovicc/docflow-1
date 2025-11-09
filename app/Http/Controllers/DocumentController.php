<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Department;
use App\Models\Document;
use App\Services\CacheService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DocumentController extends Controller
{
    protected CacheService $cacheService;

    public function __construct(CacheService $cacheService)
    {
        $this->cacheService = $cacheService;
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Подготавливаем фильтры
        $filters = [
            'status' => $request->filled('status') ? $request->status : null,
            'category_id' => $request->filled('category') ? $request->category : null,
            'department_id' => $request->filled('department') ? $request->department : null,
            'search' => $request->filled('search') ? $request->search : null,
        ];

        // Если не админ, ограничиваем по отделу
        if (!Auth::user()->can('system.admin')) {
            $filters['department_id'] = Auth::user()->department_id;
        }

        $page = $request->get('page', 1);
        
        // Получаем документы из кеша или базы данных
        $documents = $this->cacheService->getDocumentsList($filters, $page, 10);
        
        // Получаем вспомогательные данные из кеша
        $categories = $this->cacheService->getCategoriesList()->where('is_active', true);
        $departments = $this->cacheService->getDepartmentsList();

        return view('documents.index', compact('documents', 'categories', 'departments'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = Category::where('is_active', true)->get();
        $departments = Department::all();
        
        return view('documents.create', compact('categories', 'departments'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'required|exists:categories,id',
            'department_id' => 'required|exists:departments,id',
            'due_date' => 'nullable|date|after:today',
            'files' => 'nullable|array',
            'files.*' => 'file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
        ]);

        $document = Document::create([
            'title' => $request->title,
            'description' => $request->description,
            'author_id' => Auth::id(),
            'category_id' => $request->category_id,
            'department_id' => $request->department_id,
            'due_date' => $request->due_date,
            'status' => 'draft',
        ]);

        // Загружаем файлы
        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                try {
                    $document->addMedia($file)
                        ->toMediaCollection('documents');
                } catch (\Exception $e) {
                    \Log::error('Ошибка загрузки файла: ' . $e->getMessage());
                    // Продолжаем выполнение, но логируем ошибку
                }
            }
        }

        return redirect()->route('documents.index')
            ->with('success', 'Документ успешно создан.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Document $document)
    {
        $document->load(['author', 'category', 'department']);
        return view('documents.show', compact('document'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Document $document)
    {
        $categories = Category::where('is_active', true)->get();
        $departments = Department::all();
        
        return view('documents.edit', compact('document', 'categories', 'departments'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Document $document)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'required|exists:categories,id',
            'department_id' => 'required|exists:departments,id',
            'due_date' => 'nullable|date|after:today',
            'files' => 'nullable|array',
            'files.*' => 'file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
        ]);

        $document->update($request->only([
            'title', 'description', 'category_id', 'department_id', 'due_date'
        ]));

        // Добавляем новые файлы
        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                try {
                    $document->addMedia($file)->toMediaCollection('documents');
                } catch (\Exception $e) {
                    \Log::error('Ошибка загрузки файла при обновлении: ' . $e->getMessage());
                    // Продолжаем выполнение, но логируем ошибку
                }
            }
        }

        return redirect()->route('documents.index')
            ->with('success', 'Документ успешно обновлён.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Document $document)
    {
        $document->delete();

        return redirect()->route('documents.index')
            ->with('success', 'Документ успешно удалён.');
    }
}
