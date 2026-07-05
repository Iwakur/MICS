<?php

/**
 * MICS test coverage: tests Feature DatabaseSeederTest. See docs/file-reference.md for protected behavior.
 */

namespace Tests\Feature;

use App\Models\BankMonth;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\LessonType;
use App\Models\Payment;
use App\Models\Plan;
use App\Models\Staff;
use App\Models\StaffRole;
use App\Models\Student;
use App\Models\StudentMonth;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class DatabaseSeederTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_database_seeder_creates_connected_demo_data_idempotently(): void
    {
        $this->seed(DatabaseSeeder::class);
        $this->seed(DatabaseSeeder::class);

        $this->assertSame(3, StaffRole::query()->count());
        $this->assertSame(3, LessonType::query()->count());
        $this->assertSame(3, Plan::query()->count());
        $this->assertSame(4, ExpenseCategory::query()->count());
        $this->assertSame(3, Staff::query()->count());
        $this->assertSame(2, User::query()->count());
        $this->assertSame(3, Student::query()->count());
        $this->assertSame(3, StudentMonth::query()->count());
        $this->assertSame(3, Payment::query()->count());
        $this->assertSame(3, Expense::query()->count());
        $this->assertSame(2, BankMonth::query()->count());

        $teacher = User::query()->where('username', 'teacher')->firstOrFail();
        $this->assertTrue($teacher->staffMember->role->can_teach);
        $this->assertSame(2, $teacher->staffMember->students()->count());
    }
}
