<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProjectExpenseRequest;
use App\Models\Employee;
use App\Models\Project;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ProjectExpenseController extends Controller
{
    public function create(Project $project): View
    {
        return view('projects.expenses.form', [
            'project' => $project,
            'employees' => Employee::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(ProjectExpenseRequest $request, Project $project): RedirectResponse
    {
        $project->expenses()->create([
            ...$request->validated(),
            'recorded_by_user_id' => $request->user()->id,
        ]);

        return redirect()
            ->route('projects.show', $project)
            ->with('success', 'Project site expense recorded. It has not been added to the company expense or cash ledger.');
    }
}
