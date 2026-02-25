<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use App\Models\TaskReport;
use App\Enums\TaskStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Controller;

class TaskController extends Controller
{
    public function index(Project $project)
    {
        $this->authorize('view', $project);

        $tasks = $project->tasks()
            ->with(['assignee', 'creator'])
            ->withCount('comments') // Добавить эту строку
            ->when(request('status'), function ($query, $status) {
                return $query->where('status', $status);
            })
            ->latest()
            ->paginate(15);

        return view('tasks.index', compact('project', 'tasks'));
    }

    public function create(Project $project)
    {
        $this->authorize('createTask', $project);

        $siteManagers = $project->participants()
            ->wherePivot('role', 'site_manager')
            ->get();

        return view('tasks.create', compact('project', 'siteManagers'));
    }

    public function store(Request $request, Project $project)
    {
        $this->authorize('createTask', $project);

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'assigned_to' => 'required|exists:users,id',
        ]);

        Task::create([
            'company_id' => $project->company_id,
            'project_id' => $project->id,
            'created_by' => Auth::id(),
            'assigned_to' => $request->assigned_to,
            'title' => $request->title,
            'description' => $request->description,
            'status' => TaskStatus::SENT->value,
        ]);

        return redirect()->route('projects.tasks.index', $project)
            ->with('success', 'Задача создана');
    }

    public function show(Project $project, Task $task)
    {
        $this->authorize('view', $project);

        $task->load(['assignee', 'creator', 'reports' => fn($q) => $q->latest()->with('user')]);

        return view('tasks.show', compact('project', 'task'));
    }

    public function start(Task $task)
    {
        $this->authorize('update', $task);

        if ($task->status !== TaskStatus::SENT->value) {
            return back()->with('error', 'Задачу можно принять только в статусе "Отправлено"');
        }

        $task->update(['status' => TaskStatus::IN_PROGRESS->value]);

        return back()->with('success', 'Задача принята в работу');
    }

    public function report(Request $request, Task $task)
    {
        $this->authorize('update', $task);

        $request->validate([
            'report_text' => 'required|string',
            'photo' => 'nullable|image|max:5120',
        ]);

        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('task-reports/' . $task->id, 'public');
        }

        TaskReport::create([
            'company_id' => $task->company_id,
            'task_id' => $task->id,
            'user_id' => Auth::id(),
            'report_text' => $request->report_text,
            'photo_path' => $photoPath,
        ]);

        return back()->with('success', 'Отчет добавлен');
    }

    public function complete(Task $task)
    {
        $this->authorize('update', $task);

        if ($task->status !== TaskStatus::IN_PROGRESS->value) {
            return back()->with('error', 'Задачу можно завершить только в статусе "В работе"');
        }

        $task->update(['status' => TaskStatus::COMPLETED->value]);

        return back()->with('success', 'Задача выполнена');
    }
}