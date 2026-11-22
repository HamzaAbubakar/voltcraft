<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProjectRequest;
use App\Models\Employee;
use App\Models\Project;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\StreamedResponse;
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
                'COALESCE(SUM(system_size_watts), 0) as total_system_size_watts, '.
                'COALESCE(SUM(system_size_watts * customer_rate_per_watt), 0) as total_contract_value, '.
                'COALESCE(SUM(system_size_watts * customer_rate_per_watt - system_size_watts * outsourced_rate_per_watt), 0) as gross_margin_total, '.
                'COALESCE(SUM(system_size_watts * outsourced_rate_per_watt), 0) as total_outsourcing_cost'
            )
            ->first();

        return view('projects.index', [
            'projects' => $projects,
            'projectCount' => (int) $totals->project_count,
            'totalSystemSizeWatts' => (float) $totals->total_system_size_watts,
            'totalContractValue' => (float) $totals->total_contract_value,
            'grossMarginTotal' => (float) $totals->gross_margin_total,
            'totalOutsourcingCost' => (float) $totals->total_outsourcing_cost,
        ]);
    }

    public function export(): StreamedResponse
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="projects_export.csv"',
        ];

        $callback = function () {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'Project Name',
                'Customer',
                'Site Location',
                'System Size (W)',
                'Customer Rate (W)',
                'Outsourced Rate (W)',
                'Contract Value',
                'Outsourcing Cost',
                'Gross Margin',
                'Status',
                'Start Date',
                'Completed Date',
                'Assigned Employee',
            ]);

            foreach (Project::query()
                ->with('assignedEmployee:id,name')
                ->orderByDesc('start_date')
                ->orderByDesc('id')
                ->cursor() as $project) {
                fputcsv($handle, [
                    $project->name,
                    $project->customer_name,
                    $project->site_location,
                    $project->system_size_watts,
                    number_format((float) $project->customer_rate_per_watt, 2),
                    number_format((float) $project->outsourced_rate_per_watt, 2),
                    number_format((float) $project->contract_value, 2),
                    number_format((float) $project->outsourcing_cost, 2),
                    number_format((float) $project->gross_margin, 2),
                    $project->status,
                    $project->start_date?->format('Y-m-d'),
                    $project->completed_date?->format('Y-m-d'),
                    $project->assignedEmployee?->name ?? '',
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
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
        $validated = $request->validated();
        $validated['system_size_watts'] = $this->convertToWatts(
            $validated['system_size_watts'],
            $request->input('system_size_unit', 'W')
        );

        $project = Project::create($validated);

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
        $validated = $request->validated();
        $validated['system_size_watts'] = $this->convertToWatts(
            $validated['system_size_watts'],
            $request->input('system_size_unit', 'W')
        );

        $project->update($validated);

        return redirect()
            ->route('projects.show', $project)
            ->with('success', 'Project updated.');
    }

    /**
     * Convert system size to watts based on unit.
     */
    private function convertToWatts(float|int $value, string $unit): int
    {
        return match (strtoupper($unit)) {
            'KW' => (int) round($value * 1000),
            'MW' => (int) round($value * 1000000),
            default => (int) $value,
        };
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
