<?php

namespace App\Http\Controllers;

use App\Actions\Cash\RecordCapitalFlowAction;
use App\Http\Requests\StoreCapitalFlowRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;

class CapitalFlowController extends Controller
{
    public function store(
        StoreCapitalFlowRequest $request,
        RecordCapitalFlowAction $recordCapitalFlow,
    ): RedirectResponse {
        $user = $request->user();
        abort_unless($user instanceof User, 403);

        $recordCapitalFlow->handle($request->validated(), $user);

        return redirect()
            ->route('dashboard')
            ->with('success', 'Capital flow activity recorded.');
    }
}
