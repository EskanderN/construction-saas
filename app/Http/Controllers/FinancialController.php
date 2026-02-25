<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\FinancialStatusLog;
use App\Enums\FinancialStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FinancialController extends Controller
{
    public function index(Project $project)
    {
        $this->authorize('viewFinancial', $project);

        $logs = $project->financialStatusLogs()
            ->with('user')
            ->latest()
            ->paginate(15);

        return view('financial.index', compact('project', 'logs'));
    }

    public function update(Request $request, Project $project)
    {
        $this->authorize('updateFinancial', $project);

        $request->validate([
            'financial_status' => 'required|string|in:' . implode(',', FinancialStatus::values()),
            'comment' => 'nullable|string',
        ]);

        FinancialStatusLog::create([
            'company_id' => $project->company_id,
            'project_id' => $project->id,
            'user_id' => Auth::id(),
            'financial_status' => $request->financial_status,
            'comment' => $request->comment,
        ]);

        return back()->with('success', 'Финансовый статус обновлен');
    }
}