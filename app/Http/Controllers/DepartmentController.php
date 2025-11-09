<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\User;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $departments = Department::with(['head', 'users'])->paginate(10);
        return view('departments.index', compact('departments'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $users = User::all();
        return view('departments.create', compact('users'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:departments',
            'description' => 'nullable|string',
            'head_id' => 'nullable|exists:users,id',
        ]);

        Department::create($request->all());

        return redirect()->route('departments.index')
            ->with('success', 'Отдел успешно создан.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Department $department)
    {
        $department->load(['head', 'users']);
        return view('departments.show', compact('department'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Department $department)
    {
        $users = User::all();
        return view('departments.edit', compact('department', 'users'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Department $department)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:departments,name,' . $department->id,
            'description' => 'nullable|string',
            'head_id' => 'nullable|exists:users,id',
        ]);

        $department->update($request->all());

        return redirect()->route('departments.index')
            ->with('success', 'Отдел успешно обновлён.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Department $department)
    {
        // Проверяем, есть ли пользователи в отделе
        if ($department->users()->count() > 0) {
            return redirect()->route('departments.index')
                ->with('error', 'Нельзя удалить отдел, в котором есть сотрудники.');
        }

        $department->delete();

        return redirect()->route('departments.index')
            ->with('success', 'Отдел успешно удалён.');
    }
}
