<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'employee_id',
    'work_date',
    'overtime_hours',
    'overtime_hourly_rate',
    'overtime_amount',
    'commission_amount',
])]
class EmployeeDailyCompensation extends Model
{
    /**
     * Get the employee this daily compensation belongs to.
     *
     * @return BelongsTo<Employee, $this>
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'work_date' => 'date',
            'overtime_hours' => 'decimal:2',
            'overtime_hourly_rate' => 'decimal:2',
            'overtime_amount' => 'decimal:2',
            'commission_amount' => 'decimal:2',
        ];
    }
}
