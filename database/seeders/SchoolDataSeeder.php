<?php

namespace Database\Seeders;

use App\Enums\StaffCompensationMode;
use App\Enums\StudentBillingType;
use App\Enums\StudentStatus;
use App\Enums\UserRole;
use App\Models\LessonType;
use App\Models\Plan;
use App\Models\Staff;
use App\Models\StaffRole;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Seeder;

class SchoolDataSeeder extends Seeder
{
    private const string SEED_DATE = '2026-04-01';

    public function run(): void
    {
        $teacherRole = StaffRole::query()->where('name', 'Викладач')->firstOrFail();

        $adminStaff = Staff::query()->updateOrCreate(
            ['email' => 'admin.staff@example.com'],
            [
                'staff_role_id' => $teacherRole->id,
                'first_name' => 'Адміністратор',
                'family_name' => 'MICS HUB',
                'compensation_mode' => StaffCompensationMode::Dynamic,
                'salary_amount' => null,
                'is_active' => true,
                'note' => 'Адміністративний обліковий запис із профілем викладача.',
            ],
        );

        $teacherStaff = Staff::query()->updateOrCreate(
            ['email' => 'teacher@example.com'],
            [
                'staff_role_id' => $teacherRole->id,
                'first_name' => 'Максим',
                'family_name' => 'Гузьо',
                'father_name' => 'Степанович',
                'compensation_mode' => StaffCompensationMode::Dynamic,
                'salary_amount' => null,
                'is_active' => true,
                'note' => 'Викладач із джерела «Customers DB».',
            ],
        );

        $this->account('admin', 'admin@example.com', UserRole::Admin, $adminStaff);
        $this->account('teacher', 'teacher@example.com', UserRole::Teacher, $teacherStaff);
        $this->students($teacherStaff);
    }

    private function account(string $username, string $email, UserRole $role, Staff $staff): void
    {
        User::query()->updateOrCreate(
            ['username' => $username],
            [
                'staff_id' => $staff->id,
                'email' => $email,
                'password' => 'password',
                'role' => $role,
                'is_active' => true,
                'locale' => 'en',
            ],
        );
    }

    private function students(Staff $teacher): void
    {
        $lesson300 = LessonType::query()->where('name', 'Індивідуальний урок 300 ₴')->firstOrFail();
        $lesson350 = LessonType::query()->where('name', 'Індивідуальний урок 350 ₴')->firstOrFail();
        $basicPlan = Plan::query()->where('name', 'Базовий')->firstOrFail();

        $students = [
            ['name' => 'Олексій Кравченко', 'lesson_type_id' => $lesson300->id],
            ['name' => 'Горбатько Олексій', 'lesson_type_id' => $lesson300->id],
            ['name' => 'Кузін Микита', 'lesson_type_id' => $lesson300->id, 'discount_amount' => 720],
            ['name' => 'Максим Гринько', 'lesson_type_id' => $lesson300->id],
            ['name' => 'Олег Нікітін', 'lesson_type_id' => $lesson300->id],
            ['name' => 'Кірієнко Юрій', 'lesson_type_id' => $lesson300->id],
            ['name' => 'Паша Семініхін', 'plan_id' => $basicPlan->id, 'discount_amount' => 1000],
            ['name' => 'Сагдіс Ліда', 'lesson_type_id' => $lesson350->id],
            ['name' => 'Сагдіс Тімур', 'lesson_type_id' => $lesson350->id],
            ['name' => 'Стрельников Данііл', 'lesson_type_id' => $lesson300->id, 'discount_amount' => 1200],
            ['name' => 'Литвиненко Кирило', 'lesson_type_id' => $lesson300->id, 'discount_amount' => 1200],
            ['name' => 'Кириленко Сергій', 'lesson_type_id' => $lesson300->id, 'discount_amount' => 1200],
        ];

        foreach ($students as $row) {
            $isPlan = isset($row['plan_id']);
            Student::query()->updateOrCreate(
                ['staff_id' => $teacher->id, 'first_name' => $row['name']],
                [
                    'family_name' => null,
                    'joined_at' => self::SEED_DATE,
                    'status' => StudentStatus::Active,
                    'billing_type' => $isPlan ? StudentBillingType::PlanBased : StudentBillingType::PerLesson,
                    'lesson_type_id' => $row['lesson_type_id'] ?? null,
                    'plan_id' => $row['plan_id'] ?? null,
                    'plan_start_at' => $isPlan ? self::SEED_DATE : null,
                    'discount_amount' => $row['discount_amount'] ?? 0,
                    'note' => 'Джерело: Травень26.',
                ],
            );
        }
    }
}
