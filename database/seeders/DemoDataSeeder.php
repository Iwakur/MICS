<?php

namespace Database\Seeders;

use App\Enums\ReviewStatus;
use App\Enums\StaffCompensationMode;
use App\Enums\StudentBillingType;
use App\Enums\StudentStatus;
use App\Enums\UserRole;
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
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $teacherRole = StaffRole::query()->where('name', 'Teacher')->firstOrFail();
        $managerRole = StaffRole::query()->where('name', 'Manager')->firstOrFail();

        $adminStaff = $this->staff('admin.staff@example.com', [
            'staff_role_id' => $teacherRole->id,
            'first_name' => 'MICS',
            'family_name' => 'Administrator',
            'phone' => '+32 470 000 001',
            'city' => 'Brussels',
            'compensation_mode' => StaffCompensationMode::Dynamic,
            'salary_amount' => null,
            'note' => 'Demo administrator and teaching staff profile.',
        ]);

        $teacherStaff = $this->staff('amina.teacher@example.com', [
            'staff_role_id' => $teacherRole->id,
            'first_name' => 'Amina',
            'family_name' => 'Karim',
            'phone' => '+32 470 000 002',
            'city' => 'Brussels',
            'compensation_mode' => StaffCompensationMode::Dynamic,
            'salary_amount' => null,
            'note' => 'Demo teacher with mixed student billing.',
        ]);

        $managerStaff = $this->staff('leila.manager@example.com', [
            'staff_role_id' => $managerRole->id,
            'first_name' => 'Leila',
            'family_name' => 'Martin',
            'phone' => '+32 470 000 003',
            'city' => 'Antwerp',
            'compensation_mode' => StaffCompensationMode::Fixed,
            'salary_amount' => 2400,
            'note' => 'Demo fixed-salary operations manager.',
        ]);

        $this->account('admin', 'admin@example.com', UserRole::Admin, $adminStaff);
        $this->account('teacher', 'teacher@example.com', UserRole::Teacher, $teacherStaff);

        $standardLesson = LessonType::query()->where('name', 'Standard 45')->firstOrFail();
        $extendedLesson = LessonType::query()->where('name', 'Extended 60')->firstOrFail();
        $standardPlan = Plan::query()->where('name', 'Standard Monthly')->firstOrFail();

        $amina = $this->student('amina.student@example.com', [
            'staff_id' => $teacherStaff->id,
            'first_name' => 'Amina',
            'family_name' => 'Nouri',
            'phone' => '+32 480 100 001',
            'city' => 'Brussels',
            'joined_at' => '2026-05-10',
            'billing_type' => StudentBillingType::PerLesson,
            'lesson_type_id' => $standardLesson->id,
            'plan_id' => null,
            'plan_start_at' => null,
            'discount_amount' => 0,
        ]);

        $youssef = $this->student('youssef.student@example.com', [
            'staff_id' => $teacherStaff->id,
            'first_name' => 'Youssef',
            'family_name' => 'Benali',
            'phone' => '+32 480 100 002',
            'city' => 'Brussels',
            'joined_at' => '2026-06-02',
            'billing_type' => StudentBillingType::PerLesson,
            'lesson_type_id' => $extendedLesson->id,
            'plan_id' => null,
            'plan_start_at' => null,
            'discount_amount' => 10,
        ]);

        $sara = $this->student('sara.student@example.com', [
            'staff_id' => $adminStaff->id,
            'first_name' => 'Sara',
            'family_name' => 'De Smet',
            'phone' => '+32 480 100 003',
            'city' => 'Ghent',
            'joined_at' => '2026-06-15',
            'billing_type' => StudentBillingType::PlanBased,
            'lesson_type_id' => null,
            'plan_id' => $standardPlan->id,
            'plan_start_at' => '2026-06-15',
            'discount_amount' => 20,
        ]);

        $aminaMonth = $this->studentMonth($amina, 280, 0);
        $youssefMonth = $this->studentMonth($youssef, 350, -10);
        $saraMonth = $this->studentMonth($sara, 310, 0);

        $this->payment($aminaMonth, 280, ReviewStatus::Validated, 'Demo payment: Amina July');
        $this->payment($youssefMonth, 200, ReviewStatus::Draft, 'Demo payment: Youssef July');
        $this->payment($saraMonth, 310, ReviewStatus::Validated, 'Demo payment: Sara July');

        $this->expenses($managerStaff);
        $this->bankMonths();
    }

    private function staff(string $email, array $attributes): Staff
    {
        return Staff::query()->updateOrCreate(['email' => $email], $attributes + ['is_active' => true]);
    }

    private function account(string $username, string $email, UserRole $role, Staff $staff): void
    {
        $user = User::query()->firstOrCreate(
            ['username' => $username],
            ['email' => $email, 'password' => 'password', 'role' => $role, 'is_active' => true],
        );

        $user->update([
            'staff_id' => $staff->id,
            'password' => 'password',
            'role' => $role,
            'is_active' => true,
        ]);
    }

    private function student(string $email, array $attributes): Student
    {
        return Student::query()->updateOrCreate(
            ['email' => $email],
            $attributes + [
                'status' => StudentStatus::Active,
                'lesson_amount' => null,
                'note' => 'Seeded demo student.',
            ],
        );
    }

    private function studentMonth(Student $student, float $charge, float $adjustment): StudentMonth
    {
        return StudentMonth::query()->updateOrCreate(
            ['student_id' => $student->id, 'month_date' => CarbonImmutable::parse('2026-07-01')],
            ['opening_balance' => 0, 'charge_amount' => $charge, 'manual_adjustment' => $adjustment, 'note' => 'Seeded July demo month.'],
        );
    }

    private function payment(StudentMonth $month, float $amount, ReviewStatus $status, string $note): void
    {
        Payment::query()->updateOrCreate(
            ['student_month_id' => $month->id, 'note' => $note],
            ['paid_at' => '2026-07-25 12:00:00', 'amount' => $amount, 'payment_method' => 'bank_transfer', 'status' => $status],
        );
    }

    private function expenses(Staff $manager): void
    {
        $rows = [
            ['category' => 'Rent', 'amount' => 1800, 'status' => ReviewStatus::Validated, 'note' => 'Demo expense: July rent'],
            ['category' => 'Utilities', 'amount' => 240, 'status' => ReviewStatus::Draft, 'note' => 'Demo expense: July utilities'],
            ['category' => 'Supplies', 'amount' => 125, 'status' => ReviewStatus::Validated, 'note' => 'Demo expense: July supplies'],
        ];

        foreach ($rows as $row) {
            $category = ExpenseCategory::query()->where('name', $row['category'])->firstOrFail();
            Expense::query()->updateOrCreate(
                ['note' => $row['note']],
                [
                    'staff_id' => $manager->id,
                    'expense_category_id' => $category->id,
                    'month_date' => '2026-07-01',
                    'amount' => $row['amount'],
                    'status' => $row['status'],
                    'is_auto_generated' => false,
                ],
            );
        }
    }

    private function bankMonths(): void
    {
        BankMonth::query()->updateOrCreate(
            ['month_date' => CarbonImmutable::parse('2026-06-01')],
            ['opening_balance' => 12000, 'closing_balance' => 13750, 'note' => 'Seeded June bank snapshot.'],
        );
        BankMonth::query()->updateOrCreate(
            ['month_date' => CarbonImmutable::parse('2026-07-01')],
            ['opening_balance' => 13750, 'closing_balance' => 0, 'note' => 'Seeded open July bank snapshot.'],
        );
    }
}
