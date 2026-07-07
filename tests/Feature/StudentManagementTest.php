<?php

/**
 * MICS HUB test coverage: tests Feature StudentManagementTest. See docs/file-reference.md for protected behavior.
 */

namespace Tests\Feature;

use App\Enums\StaffCompensationMode;
use App\Enums\StudentBillingType;
use App\Enums\StudentStatus;
use App\Models\LessonType;
use App\Models\Plan;
use App\Models\Staff;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class StudentManagementTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_admin_can_create_a_per_lesson_student(): void
    {
        $admin = User::factory()->admin()->create();
        $teacher = Staff::factory()->create(['compensation_mode' => StaffCompensationMode::Dynamic]);
        $lessonType = LessonType::factory()->create();

        $response = $this->actingAs($admin)->post(route('admin.students.store'), $this->studentData([
            'staff_id' => $teacher->id,
            'billing_type' => StudentBillingType::PerLesson->value,
            'lesson_type_id' => $lessonType->id,
            'plan_id' => Plan::factory()->create()->id,
            'plan_start_at' => '2026-07-01',
        ]));

        $response->assertRedirect(route('admin.students.index'));

        $student = Student::query()->where('first_name', 'Amina')->firstOrFail();
        $this->assertTrue($student->teacher->is($teacher));
        $this->assertTrue($student->lessonType->is($lessonType));
        $this->assertNull($student->plan_id);
        $this->assertNull($student->plan_start_at);
        $this->assertNull($student->lesson_amount);
    }

    public function test_student_index_is_paginated(): void
    {
        $admin = User::factory()->admin()->create();
        $teacher = Staff::factory()->create();
        $lessonType = LessonType::factory()->create();

        foreach (range(1, 26) as $number) {
            Student::factory()->create([
                'staff_id' => $teacher->id,
                'lesson_type_id' => $lessonType->id,
                'first_name' => sprintf('Student %02d', $number),
            ]);
        }

        $this->actingAs($admin)
            ->get(route('admin.students.index'))
            ->assertOk()
            ->assertDontSeeText('Student 26');

        $this->actingAs($admin)
            ->get(route('admin.students.index', ['page' => 2]))
            ->assertOk()
            ->assertSeeText('Student 26');
    }

    public function test_plan_based_student_requires_a_plan_and_start_date(): void
    {
        $admin = User::factory()->admin()->create();
        $teacher = Staff::factory()->create();

        $response = $this->actingAs($admin)->post(route('admin.students.store'), $this->studentData([
            'staff_id' => $teacher->id,
            'billing_type' => StudentBillingType::PlanBased->value,
            'lesson_type_id' => null,
            'plan_id' => null,
            'plan_start_at' => null,
        ]));

        $response->assertInvalid(['plan_id', 'plan_start_at']);
    }

    public function test_admin_cannot_assign_a_student_to_non_teaching_staff(): void
    {
        $admin = User::factory()->admin()->create();
        $staff = Staff::factory()->create();
        $staff->role->update(['can_teach' => false]);

        $response = $this->actingAs($admin)->post(route('admin.students.store'), $this->studentData([
            'staff_id' => $staff->id,
        ]));

        $response->assertInvalid('staff_id');
        $this->assertDatabaseMissing('students', ['first_name' => 'Amina', 'staff_id' => $staff->id]);
    }

    public function test_admin_archives_a_student_without_deleting_it(): void
    {
        $admin = User::factory()->admin()->create();
        $student = Student::factory()->create();

        $this->actingAs($admin)
            ->delete(route('admin.students.destroy', $student))
            ->assertRedirect(route('admin.students.index'));

        $this->assertSame(StudentStatus::Archived, $student->refresh()->status);
    }

    public function test_teacher_can_create_a_student_only_for_their_own_profile(): void
    {
        $staff = Staff::factory()->create(['is_active' => true]);
        $teacher = User::factory()->teacher()->for($staff, 'staffMember')->create();
        $otherStaff = Staff::factory()->create();
        $lessonType = LessonType::factory()->create();

        $response = $this->actingAs($teacher)->post(route('teacher.students.store'), $this->studentData([
            'staff_id' => $otherStaff->id,
            'lesson_type_id' => $lessonType->id,
        ]));

        $response->assertRedirect(route('teacher.students.index'));
        $this->assertDatabaseHas('students', [
            'first_name' => 'Amina',
            'staff_id' => $staff->id,
        ]);
        $this->assertDatabaseMissing('students', [
            'first_name' => 'Amina',
            'staff_id' => $otherStaff->id,
        ]);
    }

    public function test_teacher_cannot_access_another_teachers_student(): void
    {
        $staff = Staff::factory()->create(['is_active' => true]);
        $teacher = User::factory()->teacher()->for($staff, 'staffMember')->create();
        $otherStudent = Student::factory()->create();

        $this->actingAs($teacher)
            ->get(route('teacher.students.edit', $otherStudent))
            ->assertForbidden();

        $this->actingAs($teacher)
            ->put(route('teacher.students.update', $otherStudent), $this->studentData())
            ->assertForbidden();
    }

    public function test_teacher_cannot_archive_a_student_through_status(): void
    {
        $staff = Staff::factory()->create(['is_active' => true]);
        $teacher = User::factory()->teacher()->for($staff, 'staffMember')->create();
        $student = Student::factory()->for($staff, 'teacher')->create();

        $response = $this->actingAs($teacher)->put(route('teacher.students.update', $student), $this->studentData([
            'status' => StudentStatus::Archived->value,
            'lesson_type_id' => $student->lesson_type_id,
        ]));

        $response->assertInvalid('status');
        $this->assertNotSame(StudentStatus::Archived, $student->refresh()->status);
    }

    public function test_teacher_can_open_the_edit_form_with_the_current_archived_lesson_type(): void
    {
        $staff = Staff::factory()->create(['is_active' => true]);
        $teacher = User::factory()->teacher()->for($staff, 'staffMember')->create();
        $lessonType = LessonType::factory()->create(['is_assignable' => false]);
        $student = Student::factory()->for($staff, 'teacher')->for($lessonType)->create();

        $this->actingAs($teacher)
            ->get(route('teacher.students.edit', $student))
            ->assertOk()
            ->assertSee($lessonType->name);
    }

    private function studentData(array $overrides = []): array
    {
        return array_merge([
            'first_name' => 'Amina',
            'family_name' => 'Khan',
            'father_name' => null,
            'email' => 'amina.student@example.com',
            'phone' => '555-100',
            'birthday' => '2010-03-10',
            'city' => 'Brussels',
            'joined_at' => '2026-07-01',
            'status' => StudentStatus::Active->value,
            'billing_type' => StudentBillingType::PerLesson->value,
            'lesson_type_id' => LessonType::factory()->create()->id,
            'plan_id' => null,
            'plan_start_at' => null,
            'discount_amount' => '0',
            'note' => null,
        ], $overrides);
    }
}
