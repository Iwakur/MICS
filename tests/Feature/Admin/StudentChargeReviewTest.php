<?php

/**
 * MICS HUB test coverage: tests Feature Admin StudentChargeReviewTest. See docs/file-reference.md for protected behavior.
 */

namespace Tests\Feature\Admin;

use App\Enums\ReviewStatus;
use App\Models\Payment;
use App\Models\Student;
use App\Models\StudentMonth;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class StudentChargeReviewTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_admin_adjusts_and_validates_charge_with_auditable_carry_forward(): void
    {
        $admin = User::factory()->admin()->create();
        $student = Student::factory()->create();
        $month = StudentMonth::factory()->for($student)->create([
            'month_date' => '2026-07-01', 'opening_balance' => 20, 'charge_amount' => 100,
            'manual_adjustment' => 0, 'status' => ReviewStatus::Draft,
        ]);
        Payment::factory()->validated()->for($month)->create(['amount' => 40]);

        $this->actingAs($admin)->put(route('admin.student-charges.update', $month), [
            'manual_adjustment' => -10, 'adjustment_reason' => 'One cancelled lesson',
            'status' => 'validated', 'note' => 'Approved by manager',
        ])->assertRedirect(route('admin.student-charges.index', ['month' => '2026-07']));

        $month->refresh();
        $this->assertSame('-10.00', $month->manual_adjustment);
        $this->assertSame(ReviewStatus::Validated, $month->status);
        $this->assertSame($admin->id, $month->adjusted_by_user_id);
        $this->assertSame($admin->id, $month->validated_by_user_id);
        $this->assertNotNull($month->validated_at);
        $this->assertSame('70.00', StudentMonth::query()->whereBelongsTo($student)->whereDate('month_date', '2026-08-01')->firstOrFail()->opening_balance);
        $this->actingAs($admin)->put(route('admin.student-charges.update', $month), [])->assertForbidden();
    }

    public function test_adjustment_requires_a_reason(): void
    {
        $admin = User::factory()->admin()->create();
        $month = StudentMonth::factory()->create(['status' => ReviewStatus::Draft]);

        $this->actingAs($admin)->put(route('admin.student-charges.update', $month), [
            'manual_adjustment' => 5, 'adjustment_reason' => '', 'status' => 'draft',
        ])->assertInvalid('adjustment_reason');
    }
}
