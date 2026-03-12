<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TaskController extends Controller
{
    public function index(Request $request) {
        if ($request->wantsJson()) {
            $tasks = Task::query()
                ->when($request->start, function ($query, $start) {
                    $query->where('start', '>=', $start);
                })
                ->when($request->end, function ($query, $end) {
                    $query->where('start', '<=', $end);
                })
                ->get(['id', 'title', 'start', 'end', 'color', 'description']);
            return response()->json($tasks);
        }
        return Inertia::render('Tasks/Index');
    }

    public function store(Request $request) {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start' => 'required|date',
            'end' => 'nullable|date|after_or_equal:start',
            'color' => 'nullable|string',
        ]);
        Task::create($validated);
        return redirect()->back()->with('success', 'Задача создана!');
    }

    public function update(Request $request, Task $task) {
        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'start' => 'sometimes|required|date',
            'end' => 'nullable|date|after_or_equal:start',
            'color' => 'nullable|string',
        ]);
        $task->update($validated);
        return redirect()->back()->with('success', 'Задача обновлена!');
    }

    public function destroy(Task $task) {
        $task->delete();
        return redirect()->back()->with('success', 'Задача удалена!');
    }
}
