<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkforceManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_workforce_management(): void
    {
        $this->get(route('workforce.index'))
            ->assertRedirect(route('login'));

        $this->get(route('workforce.employees.index'))
            ->assertRedirect(route('login'));

        $this->get(route('workforce.daily-compensations.create'))
            ->assertRedirect(route('login'));
    }

    public function test_monthly_workforce_totals_only_include_compensation_for_the_selected_month(): void
    {
        $user = User::factory()->create();
        $employee = Employee::factory()->create([
            'name' => 'Ayesha Khan',
            'base_salary' => 3200,
        ]);
        $employee->compensations()->create([
            'month' => '2026-11',
            'base_salary' => 3200,
            'overtime_hours' => 10,
            'overtime_amount' => 450,
            'commission_amount' => 200,
        ]);
        $employee->compensations()->create([
            'month' => '2026-10',
            'base_salary' => 3000,
            'overtime_hours' => 2,
            'overtime_amount' => 90,
            'commission_amount' => 100,
        ]);

        $this->actingAs($user)
            ->get(route('workforce.index', ['month' => '2026-11']))
            ->assertOk()
            ->assertViewHas('selectedMonth', '2026-11')
            ->assertViewHas('totals', fn (array $totals): bool => $totals['base_salary'] === 3200.0
                && $totals['overtime_amount'] === 450.0
                && $totals['commission_amount'] === 200.0
                && $totals['gross_pay'] === 3850.0)
            ->assertSee('Ayesha Khan')
            ->assertSee('Rs 3,850.00');
    }

    public function test_employee_can_be_added_and_updated_without_changing_past_compensation(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('workforce.employees.store'), [
            'name' => 'Bilal Ahmed',
            'position' => 'Technician',
            'base_salary' => '3200.00',
            'overtime_hourly_rate' => '30.00',
            'is_active' => '1',
        ]);

        $employee = Employee::query()->where('name', 'Bilal Ahmed')->firstOrFail();
        $response->assertRedirect(route('workforce.employees.index'))
            ->assertSessionHasNoErrors();
        $this->assertDatabaseHas('employees', [
            'id' => $employee->id,
            'name' => 'Bilal Ahmed',
            'position' => 'Technician',
            'base_salary' => 3200,
            'overtime_hourly_rate' => 30,
            'is_active' => true,
        ]);

        $employee->compensations()->create([
            'month' => '2026-11',
            'base_salary' => $employee->base_salary,
        ]);

        $this->put(route('workforce.employees.update', $employee), [
            'name' => 'Bilal Ahmed',
            'position' => 'Senior Technician',
            'base_salary' => '4000.00',
            'overtime_hourly_rate' => '35.00',
            'is_active' => '1',
        ])->assertRedirect(route('workforce.employees.index'))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('employees', [
            'id' => $employee->id,
            'base_salary' => 4000,
            'overtime_hourly_rate' => 35,
            'position' => 'Senior Technician',
        ]);
        $this->assertDatabaseHas('employee_compensation', [
            'employee_id' => $employee->id,
            'month' => '2026-11',
            'base_salary' => 3200,
        ]);
    }

    public function test_monthly_compensation_snapshots_salary_and_records_manual_amounts(): void
    {
        $user = User::factory()->create();
        $employee = Employee::factory()->create(['base_salary' => 3200]);

        $response = $this->actingAs($user)->post(route('workforce.compensations.store'), [
            'employee_id' => $employee->id,
            'month' => '2026-11',
            'base_salary' => '1.00',
            'overtime_hours' => '10.5',
            'overtime_amount' => '450.00',
            'commission_amount' => '200.00',
        ]);

        $response->assertRedirect(route('workforce.index', ['month' => '2026-11']))
            ->assertSessionHasNoErrors();
        $this->assertDatabaseHas('employee_compensation', [
            'employee_id' => $employee->id,
            'month' => '2026-11',
            'base_salary' => 3200,
            'overtime_hours' => 10.5,
            'overtime_amount' => 450,
            'commission_amount' => 200,
        ]);
    }

    public function test_compensation_can_be_updated_without_changing_its_salary_snapshot(): void
    {
        $user = User::factory()->create();
        $employee = Employee::factory()->create(['base_salary' => 3200]);
        $compensation = $employee->compensations()->create([
            'month' => '2026-11',
            'base_salary' => 3000,
            'overtime_hours' => 4,
            'overtime_amount' => 180,
            'commission_amount' => 100,
        ]);

        $this->actingAs($user)
            ->put(route('workforce.compensations.update', $compensation), [
                'overtime_hours' => '8',
                'overtime_amount' => '360.00',
                'commission_amount' => '250.00',
            ])
            ->assertRedirect(route('workforce.index', ['month' => '2026-11']))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('employee_compensation', [
            'id' => $compensation->id,
            'month' => '2026-11',
            'base_salary' => 3000,
            'overtime_hours' => 8,
            'overtime_amount' => 360,
            'commission_amount' => 250,
        ]);
    }

    public function test_employee_cannot_receive_duplicate_compensation_for_the_same_month(): void
    {
        $user = User::factory()->create();
        $employee = Employee::factory()->create();
        $employee->compensations()->create([
            'month' => '2026-11',
            'base_salary' => 3200,
        ]);

        $response = $this->actingAs($user)
            ->from(route('workforce.compensations.create', ['month' => '2026-11']))
            ->post(route('workforce.compensations.store'), [
                'employee_id' => $employee->id,
                'month' => '2026-11',
                'overtime_hours' => '0',
                'overtime_amount' => '0',
                'commission_amount' => '0',
            ]);

        $response->assertSessionHasErrors('employee_id');
        $this->assertDatabaseCount('employee_compensation', 1);
    }

    public function test_negative_compensation_amounts_are_rejected(): void
    {
        $user = User::factory()->create();
        $employee = Employee::factory()->create();

        $response = $this->actingAs($user)
            ->from(route('workforce.compensations.create'))
            ->post(route('workforce.compensations.store'), [
                'employee_id' => $employee->id,
                'month' => '2026-11',
                'overtime_hours' => '0',
                'overtime_amount' => '-1.00',
                'commission_amount' => '0',
            ]);

        $response->assertSessionHasErrors('overtime_amount');
        $this->assertDatabaseCount('employee_compensation', 0);
    }

    public function test_inactive_employee_cannot_receive_new_compensation(): void
    {
        $user = User::factory()->create();
        $employee = Employee::factory()->inactive()->create();

        $response = $this->actingAs($user)
            ->from(route('workforce.compensations.create'))
            ->post(route('workforce.compensations.store'), [
                'employee_id' => $employee->id,
                'month' => '2026-11',
                'overtime_hours' => '0',
                'overtime_amount' => '0',
                'commission_amount' => '0',
            ]);

        $response->assertSessionHasErrors('employee_id');
        $this->assertDatabaseCount('employee_compensation', 0);
    }

    public function test_workforce_page_escapes_employee_names(): void
    {
        $user = User::factory()->create();
        Employee::factory()->create([
            'name' => '<script>alert(1)</script>',
            'base_salary' => 3200,
        ]);

        $this->actingAs($user)
            ->get(route('workforce.index', ['month' => '2026-11']))
            ->assertOk()
            ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)
            ->assertDontSee('<script>alert(1)</script>', false);
    }

    public function test_daily_pay_calculates_overtime_from_the_employee_hourly_rate_and_updates_month_totals(): void
    {
        $user = User::factory()->create();
        $employee = Employee::factory()->create([
            'base_salary' => 3200,
            'overtime_hourly_rate' => 30,
        ]);

        $response = $this->actingAs($user)->post(route('workforce.daily-compensations.store'), [
            'employee_id' => $employee->id,
            'work_date' => '2026-11-30',
            'overtime_hours' => '10',
            'overtime_hourly_rate' => '999.00',
            'overtime_amount' => '9999.00',
            'commission_amount' => '125.00',
        ]);

        $response->assertRedirect(route('workforce.index', ['month' => '2026-11']))
            ->assertSessionHasNoErrors();
        $this->assertDatabaseHas('employee_daily_compensation', [
            'employee_id' => $employee->id,
            'work_date' => '2026-11-30 00:00:00',
            'overtime_hours' => 10,
            'overtime_hourly_rate' => 30,
            'overtime_amount' => 300,
            'commission_amount' => 125,
        ]);
        $this->assertDatabaseHas('employee_compensation', [
            'employee_id' => $employee->id,
            'month' => '2026-11',
            'base_salary' => 3200,
        ]);

        $this->get(route('workforce.index', ['month' => '2026-11']))
            ->assertOk()
            ->assertViewHas('totals', fn (array $totals): bool => $totals['base_salary'] === 3200.0
                && $totals['overtime_amount'] === 300.0
                && $totals['commission_amount'] === 125.0
                && $totals['gross_pay'] === 3625.0)
            ->assertSee('Rs 300.00')
            ->assertSee('Rs 125.00');
    }

    public function test_daily_overtime_rate_is_snapshotted_when_a_daily_entry_is_edited(): void
    {
        $user = User::factory()->create();
        $employee = Employee::factory()->create(['overtime_hourly_rate' => 30]);

        $this->actingAs($user)->post(route('workforce.daily-compensations.store'), [
            'employee_id' => $employee->id,
            'work_date' => '2026-11-10',
            'overtime_hours' => '4',
            'commission_amount' => '50.00',
        ])->assertRedirect(route('workforce.index', ['month' => '2026-11']));

        $dailyCompensation = $employee->dailyCompensations()->firstOrFail();
        $employee->update(['overtime_hourly_rate' => 45]);

        $this->put(route('workforce.daily-compensations.update', $dailyCompensation), [
            'overtime_hours' => '5',
            'commission_amount' => '75.00',
        ])->assertRedirect(route('workforce.index', ['month' => '2026-11']))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('employee_daily_compensation', [
            'id' => $dailyCompensation->id,
            'overtime_hours' => 5,
            'overtime_hourly_rate' => 30,
            'overtime_amount' => 150,
            'commission_amount' => 75,
        ]);
    }

    public function test_employee_cannot_have_two_daily_pay_entries_for_the_same_date(): void
    {
        $user = User::factory()->create();
        $employee = Employee::factory()->create();
        $employee->dailyCompensations()->create([
            'work_date' => '2026-11-10',
            'overtime_hours' => 1,
            'overtime_hourly_rate' => 25,
            'overtime_amount' => 25,
            'commission_amount' => 0,
        ]);

        $response = $this->actingAs($user)
            ->from(route('workforce.daily-compensations.create', ['work_date' => '2026-11-10']))
            ->post(route('workforce.daily-compensations.store'), [
                'employee_id' => $employee->id,
                'work_date' => '2026-11-10',
                'overtime_hours' => '2',
                'commission_amount' => '0',
            ]);

        $response->assertSessionHasErrors('employee_id');
        $this->assertDatabaseCount('employee_daily_compensation', 1);
    }

    public function test_daily_pay_rejects_negative_overtime_hours(): void
    {
        $user = User::factory()->create();
        $employee = Employee::factory()->create();

        $response = $this->actingAs($user)
            ->from(route('workforce.daily-compensations.create'))
            ->post(route('workforce.daily-compensations.store'), [
                'employee_id' => $employee->id,
                'work_date' => '2026-11-10',
                'overtime_hours' => '-1',
                'commission_amount' => '0',
            ]);

        $response->assertSessionHasErrors('overtime_hours');
        $this->assertDatabaseCount('employee_daily_compensation', 0);
    }

    public function test_employee_cannot_be_saved_with_a_negative_overtime_hourly_rate(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->from(route('workforce.employees.create'))
            ->post(route('workforce.employees.store'), [
                'name' => 'Nadia Ali',
                'position' => 'Technician',
                'base_salary' => '3200.00',
                'overtime_hourly_rate' => '-1.00',
                'is_active' => '1',
            ]);

        $response->assertSessionHasErrors('overtime_hourly_rate');
        $this->assertDatabaseCount('employees', 0);
    }
}
