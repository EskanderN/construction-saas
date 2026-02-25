<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $companyId = $user->company_id;

        $stats = [
            'total_projects' => Project::where('company_id', $companyId)->count(),
            'active_projects' => Project::where('company_id', $companyId)
                ->whereIn('status', ['in_progress', 'on_approval', 'approved'])
                ->count(),
            'my_tasks' => Task::where('company_id', $companyId)
                ->where('assigned_to', $user->id)
                ->where('status', '!=', 'completed')
                ->count(),
        ];

        $recentProjects = Project::where('company_id', $companyId)
            ->with('creator')
            ->latest()
            ->take(5)
            ->get();

        $myTasks = Task::where('company_id', $companyId)
            ->where('assigned_to', $user->id)
            ->with(['project', 'creator'])
            ->latest()
            ->take(5)
            ->get();

        return view('dashboard.index', compact('stats', 'recentProjects', 'myTasks'));
    }
}