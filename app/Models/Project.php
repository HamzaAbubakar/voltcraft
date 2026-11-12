<?php

namespace App\Models;

use Database\Factories\ProjectFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'name',
    'customer_name',
    'site_location',
    'system_size_watts',
    'customer_rate_per_watt',
    'outsourced_rate_per_watt',
    'assigned_employee_id',
    'status',
    'start_date',
    'completed_date',
])]
class Project extends Model
{
    /** @use HasFactory<ProjectFactory> */
    use HasFactory;

    /**
     * Get the employee leading this project.
     *
     * @return BelongsTo<Employee, $this>
     */
    public function assignedEmployee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'assigned_employee_id');
    }

    /**
     * Get expenses recorded only for this project's profit calculation.
     *
     * @return HasMany<ProjectExpense, $this>
     */
    public function expenses(): HasMany
    {
        return $this->hasMany(ProjectExpense::class);
    }

    public function getContractValueAttribute(): float
    {
        return round((int) $this->system_size_watts * (float) $this->customer_rate_per_watt, 2);
    }

    public function getOutsourcingCostAttribute(): float
    {
        return round((int) $this->system_size_watts * (float) $this->outsourced_rate_per_watt, 2);
    }

    public function getGrossMarginAttribute(): float
    {
        return round($this->contract_value - $this->outsourcing_cost, 2);
    }

    public function getSiteExpensesTotalAttribute(): float
    {
        if (array_key_exists('site_expenses_total', $this->attributes)) {
            return (float) $this->attributes['site_expenses_total'];
        }

        return (float) $this->expenses->sum('amount');
    }

    public function getNetProfitAttribute(): float
    {
        return round($this->gross_margin - $this->site_expenses_total, 2);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'system_size_watts' => 'integer',
            'customer_rate_per_watt' => 'decimal:2',
            'outsourced_rate_per_watt' => 'decimal:2',
            'start_date' => 'date',
            'completed_date' => 'date',
        ];
    }
}
