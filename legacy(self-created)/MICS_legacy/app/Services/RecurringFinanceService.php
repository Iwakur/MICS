<?php

declare(strict_types=1);

namespace App\Services;

use App\Database;
use DateTimeImmutable;
use PDO;

final class RecurringFinanceService
{
    public function ensureCurrentMonthDocuments(): void
    {
        $pdo = Database::connection();
        $monthStart = current_app_datetime()->setTime(0, 0, 0)->modify('first day of this month');
        $nextMonthStart = $monthStart->modify('+1 month');
        $previousMonthStart = $monthStart->modify('-1 month');

        $this->ensureStudentCharges($pdo, $monthStart, $nextMonthStart);
        $this->ensureFixedSalaryDraftPayouts($pdo, $monthStart, $nextMonthStart, $previousMonthStart);
    }

    private function ensureStudentCharges(PDO $pdo, DateTimeImmutable $monthStart, DateTimeImmutable $nextMonthStart): void
    {
        $statement = $pdo->prepare(
            "INSERT INTO student_charges (
                student_id,
                charge_month,
                plan_id,
                staff_id,
                amount,
                status,
                created_at,
                posted_at,
                comment
             )
             SELECT
                s.id,
                :charge_month,
                s.plan_id,
                s.staff_id,
                ROUND((p.lesson_count * p.lesson_price - s.discount_amount)::numeric, 2),
                'draft',
                CURRENT_TIMESTAMP,
                NULL,
                :comment
             FROM students s
             INNER JOIN plans p ON p.id = s.plan_id
             WHERE s.status = 'active'
               AND s.joined_at < :next_month_start
               AND (p.lesson_count * p.lesson_price - s.discount_amount) > 0
             ON CONFLICT (student_id, charge_month) DO NOTHING"
        );

        $statement->execute([
            'charge_month' => $monthStart->format('Y-m-d'),
            'next_month_start' => $nextMonthStart->format('Y-m-d H:i:sP'),
            'comment' => 'System-generated charge for '.$monthStart->format('F Y').'.',
        ]);
    }

    private function ensureFixedSalaryDraftPayouts(
        PDO $pdo,
        DateTimeImmutable $monthStart,
        DateTimeImmutable $nextMonthStart,
        DateTimeImmutable $previousMonthStart
    ): void {
        $statement = $pdo->prepare(
            "INSERT INTO staff_payouts (
                staff_id,
                payout_date,
                amount,
                status,
                created_at,
                posted_at,
                comment,
                import_row_id
             )
             SELECT
                st.id,
                :payout_date,
                st.fixed_salary_amount,
                'draft',
                CURRENT_TIMESTAMP,
                NULL,
                :comment,
                NULL
             FROM staff st
             WHERE st.status = 'active'
               AND COALESCE(st.fixed_salary_amount, 0) > 0
               AND LOWER(st.role) <> 'teacher'
               AND NOT EXISTS (
                    SELECT 1
                    FROM staff_payouts sp
                    WHERE sp.staff_id = st.id
                      AND sp.payout_date >= :month_start
                      AND sp.payout_date < :next_month_start
               )"
        );

        $statement->execute([
            'payout_date' => $monthStart->format('Y-m-d H:i:sP'),
            'comment' => 'System-generated fixed salary draft for '.$previousMonthStart->format('F Y').'.',
            'month_start' => $monthStart->format('Y-m-d H:i:sP'),
            'next_month_start' => $nextMonthStart->format('Y-m-d H:i:sP'),
        ]);
    }
}
