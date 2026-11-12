<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProjectRequest;
use App\Models\Employee;
use App\Models\Project;
use App\Models\ProjectExpense;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ProjectController extends Controller
{
    public function index(): View
    {
        $projects = Project::query()
            ->with('assignedEmployee:id,name')
            ->withSum('expenses as site_expenses_total', 'amount')
            ->orderByDesc('start_date')
            ->orderByDesc('id')
            ->paginate(20);

        $totals = Project::query()
            ->selectRaw(
                'COUNT(*) as project_count, '.
                'COALESCE(SUM(system_size_watts * customer_rate_per_watt - system_size_watts * outsourced_rate_per_watt), 0) as gross_margin_total'
            )
            ->first();

        $siteExpensesTotal = (float) ProjectExpense::query()->sum('amount');

        return view('projects.index', [
            'projects' => $projects,
            'projectCount' => (int) $totals->project_count,
            'grossMarginTotal' => (float) $totals->gross_margin_total,
            'siteExpensesTotal' => $siteExpensesTotal,
            'netProfitTotal' => round((float) $totals->gross_margin_total - $siteExpensesTotal, 2),
        ]);
    }

    public function create(): View
    {
        return view('projects.form', [
            'project' => null,
            'employees' => Employee::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(ProjectRequest $request): RedirectResponse
    {
        $project = Project::create($request->validated());

        return redirect()
            ->route('projects.show', $project)
            ->with('success', 'Project created.');
    }

    public function show(Project $project): View
    {
        $project = Project::query()
            ->with([
                'assignedEmployee:id,name',
                'expenses' => fn (HasMany $query) => $query
                    ->with('employee:id,name')
                    ->orderByDesc('expense_date')
                    ->orderByDesc('id'),
            ])
            ->withSum('expenses as site_expenses_total', 'amount')
            ->findOrFail($project->id);

        return view('projects.show', ['project' => $project]);
    }

    public function edit(Project $project): View
    {
        return view('projects.form', [
            'project' => $project,
            'employees' => Employee::query()
                ->where(function (Builder $query) use ($project): void {
                    $query->where('is_active', true)
                        ->orWhereKey($project->assigned_employee_id);
                })
                ->orderBy('name')
                ->get(['id', 'name']),
        ]);
    }

    public function update(ProjectRequest $request, Project $project): RedirectResponse
    {
        $project->update($request->validated());

        return redirect()
            ->route('projects.show', $project)
            ->with('success', 'Project updated.');
    }

    public function complete(Project $project): RedirectResponse
    {
        if ($project->status !== 'active') {
            return back()->with('warning', 'Only active projects can be completed.');
        }

        $project->update([
            'status' => 'completed',
            'completed_date' => today(),
        ]);

        return redirect()
            ->route('projects.show', $project)
            ->with('success', 'Project marked as completed.');
    }
}
