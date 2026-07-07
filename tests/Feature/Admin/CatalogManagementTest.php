<?php

/**
 * MICS HUB test coverage: tests Feature Admin CatalogManagementTest. See docs/file-reference.md for protected behavior.
 */

namespace Tests\Feature\Admin;

use App\Models\LessonType;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class CatalogManagementTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_admin_can_create_and_update_a_lesson_type(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->post(route('admin.lesson-types.store'), [
            'name' => 'Private Lesson',
            'duration_minutes' => 60,
            'lesson_price' => '45.00',
            'teacher_share_per_lesson' => '25.00',
            'is_assignable' => '1',
            'note' => 'One-to-one lesson',
        ])->assertRedirect(route('admin.lesson-types.index'));

        $lessonType = LessonType::query()->where('name', 'Private Lesson')->firstOrFail();

        $this->actingAs($admin)->put(route('admin.lesson-types.update', $lessonType), [
            'name' => 'Private Lesson',
            'duration_minutes' => 75,
            'lesson_price' => '50.00',
            'teacher_share_per_lesson' => '28.50',
            'is_assignable' => '1',
            'note' => null,
        ])->assertRedirect(route('admin.lesson-types.index'));

        $lessonType->refresh();

        $this->assertSame('50.00', $lessonType->lesson_price);
        $this->assertSame('28.50', $lessonType->teacher_share_per_lesson);
        $this->assertSame(75, $lessonType->duration_minutes);
    }

    public function test_admin_can_create_and_archive_a_plan(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->post(route('admin.plans.store'), [
            'name' => 'Monthly Eight',
            'duration_minutes' => 60,
            'lesson_count' => 8.5,
            'lesson_price' => '40.00',
            'plan_price' => '300.00',
            'teacher_monthly_amount' => '180.00',
            'is_assignable' => '1',
            'note' => null,
        ])->assertRedirect(route('admin.plans.index'));

        $plan = Plan::query()->where('name', 'Monthly Eight')->firstOrFail();

        $this->assertSame('8.5', $plan->lesson_count);
        $this->assertSame('300.00', $plan->plan_price);
        $this->assertSame('180.00', $plan->teacher_monthly_amount);

        $this->actingAs($admin)
            ->delete(route('admin.plans.destroy', $plan))
            ->assertRedirect(route('admin.plans.index'));

        $this->assertFalse($plan->refresh()->is_assignable);
    }

    public function test_catalog_amounts_cannot_be_negative(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post(route('admin.lesson-types.store'), [
            'name' => 'Invalid Lesson',
            'duration_minutes' => 60,
            'lesson_price' => '-1',
            'teacher_share_per_lesson' => '-2',
            'is_assignable' => '1',
        ]);

        $response->assertInvalid(['lesson_price', 'teacher_share_per_lesson']);
    }

    public function test_teacher_cannot_manage_catalogs(): void
    {
        $teacher = User::factory()->teacher()->create();

        $this->actingAs($teacher)->get(route('admin.lesson-types.index'))->assertForbidden();
        $this->actingAs($teacher)->get(route('admin.plans.index'))->assertForbidden();
    }
}
