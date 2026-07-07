<?php

/**
 * MICS HUB test coverage: tests Feature Admin FinanceSummaryTest. See docs/file-reference.md for protected behavior.
 */

namespace Tests\Feature\Admin;

use App\Models\Expense;
use App\Models\Payment;
use App\Models\StudentMonth;
use App\Models\User;
use App\Services\FinanceSummaryService;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class FinanceSummaryTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_admin_sees_monthly_finance_totals_without_counting_drafts(): void
    {
        $admin = User::factory()->admin()->create();
        $firstMonth = StudentMonth::factory()->create([
            'month_date' => '2026-07-01',
            'opening_balance' => 50,
            'charge_amount' => 100,
            'manual_adjustment' => -10,
        ]);
        $secondMonth = StudentMonth::factory()->create([
            'month_date' => '2026-07-01',
            'opening_balance' => 0,
            'charge_amount' => 200,
        ]);
        StudentMonth::factory()->create([
            'month_date' => '2026-07-01',
            'opening_balance' => -30,
            'charge_amount' => 0,
            'manual_adjustment' => 0,
        ]);
        Payment::factory()->validated()->for($firstMonth)->create(['amount' => 40]);
        Payment::factory()->for($firstMonth)->create(['amount' => 30]);
        Payment::factory()->validated()->for($secondMonth)->create(['amount' => 50]);
        Expense::factory()->validated()->create(['month_date' => '2026-07-01', 'amount' => 300, 'is_auto_generated' => true]);
        Expense::factory()->create(['month_date' => '2026-07-01', 'amount' => 100, 'is_auto_generated' => true]);
        Expense::factory()->validated()->create(['month_date' => '2026-07-01', 'amount' => 80, 'is_auto_generated' => false]);
        Expense::factory()->create(['month_date' => '2026-07-01', 'amount' => 20, 'is_auto_generated' => false]);

        $summary = app(FinanceSummaryService::class)->forMonth(CarbonImmutable::parse('2026-07-01'));

        $this->assertSame(20.0, $summary['opening_balance']);
        $this->assertSame(290.0, $summary['charges']);
        $this->assertSame(90.0, $summary['validated_payments']);
        $this->assertSame(250.0, $summary['outstanding_debt']);
        $this->assertSame(30.0, $summary['student_credit']);
        $this->assertSame(2, $summary['students_with_debt']);
        $this->assertSame(300.0, $summary['validated_salaries']);
        $this->assertSame(100.0, $summary['draft_salaries']);
        $this->assertSame(80.0, $summary['validated_manual_expenses']);
        $this->assertSame(20.0, $summary['draft_manual_expenses']);

        $this->actingAs($admin)->get(route('admin.finance-summary', ['month' => '2026-07']))
            ->assertOk()
            ->assertSee('Monthly Finance Summary')
            ->assertSee('290.00')
            ->assertSee('250.00');
    }

    public function test_teacher_cannot_access_finance_summary(): void
    {
        $teacher = User::factory()->teacher()->create();

        $this->actingAs($teacher)->get(route('admin.finance-summary'))->assertForbidden();
    }
}
