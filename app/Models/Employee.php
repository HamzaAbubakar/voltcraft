<?php

namespace App\Models;

use Database\Factories\EmployeeFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'position', 'base_salary', 'overtime_hourly_rate', 'is_active'])]
class Employee extends Model
{
    /** @use HasFactory<EmployeeFactory> */
    use HasFactory;

    /**
     * Get the monthly compensation records for the employee.
     *
     * @return HasMany<EmployeeCompensation, $this>
     */
    public function compensations(): HasMany
    {
        return $this->hasMany(EmployeeCompensation::class);
    }

    /**
     * Get the daily compensation records for the employee.
     *
     * @return HasMany<EmployeeDailyCompensation, $this>
     */
    public function dailyCompensations(): HasMany
    {
        return $this->hasMany(EmployeeDailyCompensation::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'base_salary' => 'decimal:2',
            'overtime_hourly_rate' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }
}
