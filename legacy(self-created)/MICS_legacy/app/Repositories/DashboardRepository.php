<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Database;
use App\Services\PayoutService;
use DateTimeImmutable;
use DateTimeZone;
use PDO;

final class DashboardRepository
{
    public function adminSummary(array $selectedMonths = []): array
    {
        $pdo = Database::connection();
        $months = $this->normalizeAdminMonths($selectedMonths);
        $monthComparisons = [];

        foreach ($months as $month) {
            $monthComparisons[] = $this->adminMonthSnapshot($pdo, $month);
        }

        return [
            'selected_months' => array_map(
                static fn (DateTimeImmutable $month): string => $month->format('Y-m'),
                $months
            ),
            'current_snapshot' => [
                'students' => $this->adminStudentStatusCounts($pdo),
                'active_staff' => $this->count($pdo, "SELECT COUNT(*) FROM staff WHERE status = 'active'"),
                'archived_staff' => $this->count($pdo, "SELECT COUNT(*) FROM staff WHERE status = 'archived'"),
                'assignable_plans' => $this->count($pdo, 'SELECT COUNT(*) FROM plans WHERE is_assignable = TRUE'),
                'archived_plans' => $this->count($pdo, 'SELECT COUNT(*) FROM plans WHERE is_assignable = FALSE'),
                'pending_payments' => $this->count($pdo, "SELECT COUNT(*) FROM payments WHERE status = 'pending'"),
                'confirmed_payments' => $this->count($pdo, "SELECT COUNT(*) FROM payments WHERE status = 'confirmed'"),
                'statement_queue_rows' => $this->count($pdo, "SELECT COUNT(*) FROM statement_import_rows WHERE status IN ('new', 'unmatched')"),
                'statement_batches' => $this->count($pdo, 'SELECT COUNT(*) FROM statement_import_batches'),
                'draft_payouts' => $this->count($pdo, "SELECT COUNT(*) FROM staff_payouts WHERE status = 'draft'"),
                'posted_journal_entries' => $this->count($pdo, "SELECT COUNT(*) FROM journal_entries WHERE status = 'posted'"),
            ],
            'month_comparisons' => $monthComparisons,
            'current_distributions' => [
                'students_by_plan' => $this->studentsByPlan($pdo),
                'students_by_teacher' => $this->studentsByTeacher($pdo),
                'recent_statement_batches' => $this->recentStatementBatches($pdo),
            ],
        ];
    }

    public function teacherSummary(int $staffId): array
    {
        $pdo = Database::connection();
        $payoutService = new PayoutService;
        $payoutRepository = new PayoutRepository;
        $period = $payoutService->currentMonthPeriod();
        $monthStart = $period['month_start'];
        $nextMonthStart = $period['next_month_start'];
        $previousMonthStart = $monthStart->modify('-1 month');
        $monthStartSql = $payoutService->asSqlTimestamp($monthStart);
        $nextMonthStartSql = $payoutService->asSqlTimestamp($nextMonthStart);
        $previousMonthStartSql = $payoutService->asSqlTimestamp($previousMonthStart);

        $statusCounts = $this->studentStatusCounts($staffId);

        $plannedPayout = $payoutRepository->teacherSuggestionSummary(
            $staffId,
            $nextMonthStartSql
        );

        $postedPayoutCurrent = $this->sumPrepared(
            $pdo,
            "SELECT COALESCE(SUM(amount), 0)
             FROM staff_payouts
             WHERE staff_id = :staff_id
               AND status = 'posted'
               AND payout_date >= :month_start
               AND payout_date < :next_month_start",
            [
                'staff_id' => $staffId,
                'month_start' => $monthStartSql,
                'next_month_start' => $nextMonthStartSql,
            ]
        );

        $postedPayoutPrevious = $this->sumPrepared(
            $pdo,
            "SELECT COALESCE(SUM(amount), 0)
             FROM staff_payouts
             WHERE staff_id = :staff_id
               AND status = 'posted'
               AND payout_date >= :previous_month_start
               AND payout_date < :month_start",
            [
                'staff_id' => $staffId,
                'previous_month_start' => $previousMonthStartSql,
                'month_start' => $monthStartSql,
            ]
        );

        $confirmedPaymentsCurrent = $this->sumPrepared(
            $pdo,
            "SELECT COALESCE(SUM(p.amount), 0)
             FROM payments p
             INNER JOIN students s ON s.id = p.student_id
             WHERE s.staff_id = :staff_id
               AND p.status = 'confirmed'
               AND p.payment_date >= :month_start
               AND p.payment_date < :next_month_start",
            [
                'staff_id' => $staffId,
                'month_start' => $monthStartSql,
                'next_month_start' => $nextMonthStartSql,
            ]
        );

        $confirmedPaymentsPrevious = $this->sumPrepared(
            $pdo,
            "SELECT COALESCE(SUM(p.amount), 0)
             FROM payments p
             INNER JOIN students s ON s.id = p.student_id
             WHERE s.staff_id = :staff_id
               AND p.status = 'confirmed'
               AND p.payment_date >= :previous_month_start
               AND p.payment_date < :month_start",
            [
                'staff_id' => $staffId,
                'previous_month_start' => $previousMonthStartSql,
                'month_start' => $monthStartSql,
            ]
        );

        $postedChargesCurrent = $this->sumPrepared(
            $pdo,
            "SELECT COALESCE(SUM(amount), 0)
             FROM student_charges
             WHERE staff_id = :staff_id
               AND status = 'posted'
               AND charge_month >= :month_start_date
               AND charge_month < :next_month_start_date",
            [
                'staff_id' => $staffId,
                'month_start_date' => $monthStart->format('Y-m-d'),
                'next_month_start_date' => $nextMonthStart->format('Y-m-d'),
            ]
        );

        $postedChargesPrevious = $this->sumPrepared(
            $pdo,
            "SELECT COALESCE(SUM(amount), 0)
             FROM student_charges
             WHERE staff_id = :staff_id
               AND status = 'posted'
               AND charge_month >= :previous_month_start_date
               AND charge_month < :month_start_date",
            [
                'staff_id' => $staffId,
                'previous_month_start_date' => $previousMonthStart->format('Y-m-d'),
                'month_start_date' => $monthStart->format('Y-m-d'),
            ]
        );

        $joinedCurrent = $this->countPrepared(
            $pdo,
            'SELECT COUNT(*)
             FROM students
             WHERE staff_id = :staff_id
               AND joined_at >= :month_start
               AND joined_at < :next_month_start',
            [
                'staff_id' => $staffId,
                'month_start' => $monthStartSql,
                'next_month_start' => $nextMonthStartSql,
            ]
        );

        $joinedPrevious = $this->countPrepared(
            $pdo,
            'SELECT COUNT(*)
             FROM students
             WHERE staff_id = :staff_id
               AND joined_at >= :previous_month_start
               AND joined_at < :month_start',
            [
                'staff_id' => $staffId,
                'previous_month_start' => $previousMonthStartSql,
                'month_start' => $monthStartSql,
            ]
        );

        $recentStudents = $this->recentStudents($staffId);
        $topContributors = $this->topContributors($staffId, $nextMonthStartSql);
        $recentPayouts = $this->recentPayouts($staffId);
        $collectionGap = max(0.0, $postedChargesCurrent - $confirmedPaymentsCurrent);
        $collectionRate = $postedChargesCurrent > 0 ? ($confirmedPaymentsCurrent / $postedChargesCurrent) * 100 : null;

        return [
            'period_label' => $period['label'],
            'previous_period_label' => $previousMonthStart->format('F Y'),
            'total_students' => array_sum($statusCounts),
            'active_students' => $statusCounts['active'] ?? 0,
            'paused_students' => $statusCounts['paused'] ?? 0,
            'archived_students' => $statusCounts['archived'] ?? 0,
            'planned_payout' => (float) $plannedPayout['suggested_amount'],
            'posted_payout' => $postedPayoutCurrent,
            'confirmed_payments' => $confirmedPaymentsCurrent,
            'posted_charges' => $postedChargesCurrent,
            'collection_gap' => $collectionGap,
            'collection_rate' => $collectionRate,
            'joined_this_month' => $joinedCurrent,
            'trends' => [
                'joined_students' => [
                    'current' => $joinedCurrent,
                    'previous' => $joinedPrevious,
                    'delta' => $joinedCurrent - $joinedPrevious,
                ],
                'confirmed_payments' => [
                    'current' => $confirmedPaymentsCurrent,
                    'previous' => $confirmedPaymentsPrevious,
                    'delta' => $confirmedPaymentsCurrent - $confirmedPaymentsPrevious,
                ],
                'posted_charges' => [
                    'current' => $postedChargesCurrent,
                    'previous' => $postedChargesPrevious,
                    'delta' => $postedChargesCurrent - $postedChargesPrevious,
                ],
                'posted_payout' => [
                    'current' => $postedPayoutCurrent,
                    'previous' => $postedPayoutPrevious,
                    'delta' => $postedPayoutCurrent - $postedPayoutPrevious,
                ],
            ],
            'recent_students' => $recentStudents,
            'top_contributors' => $topContributors,
            'recent_payouts' => $recentPayouts,
        ];
    }

    private function count(PDO $pdo, string $sql): int
    {
        return (int) $pdo->query($sql)->fetchColumn();
    }

    private function sum(PDO $pdo, string $sql): float
    {
        return (float) $pdo->query($sql)->fetchColumn();
    }

    private function countPrepared(PDO $pdo, string $sql, array $params): int
    {
        $statement = $pdo->prepare($sql);
        $statement->execute($params);

        return (int) $statement->fetchColumn();
    }

    private function sumPrepared(PDO $pdo, string $sql, array $params): float
    {
        $statement = $pdo->prepare($sql);
        $statement->execute($params);

        return (float) $statement->fetchColumn();
    }

    private function adminStudentStatusCounts(PDO $pdo): array
    {
        $statement = $pdo->query(
            'SELECT status, COUNT(*) AS total
             FROM students
             GROUP BY status'
        );

        $counts = [
            'active' => 0,
            'paused' => 0,
            'archived' => 0,
        ];

        foreach ($statement->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $status = (string) ($row['status'] ?? '');
            if (array_key_exists($status, $counts)) {
                $counts[$status] = (int) ($row['total'] ?? 0);
            }
        }

        $counts['total'] = array_sum($counts);

        return $counts;
    }

    private function normalizeAdminMonths(array $selectedMonths): array
    {
        $timezone = new DateTimeZone(app_timezone());
        $normalized = [];

        foreach ($selectedMonths as $value) {
            $month = trim((string) $value);
            if (! preg_match('/^\d{4}-\d{2}$/', $month)) {
                continue;
            }

            $date = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $month.'-01 00:00:00', $timezone);
            if (! $date instanceof DateTimeImmutable) {
                continue;
            }

            $normalized[$date->format('Y-m')] = $date;
        }

        if ($normalized === []) {
            $currentMonth = current_app_datetime()->setTime(0, 0)->modify('first day of this month');
            for ($offset = 0; $offset < 3; $offset++) {
                $month = $currentMonth->modify(sprintf('-%d month', $offset));
                $normalized[$month->format('Y-m')] = $month;
            }
        }

        krsort($normalized);

        return array_values(array_slice($normalized, 0, 3));
    }

    private function adminMonthSnapshot(PDO $pdo, DateTimeImmutable $month): array
    {
        $monthStart = $month->setTime(0, 0);
        $nextMonthStart = $monthStart->modify('+1 month');
        $monthStartSql = $monthStart->format('Y-m-d H:i:s');
        $nextMonthStartSql = $nextMonthStart->format('Y-m-d H:i:s');

        return [
            'value' => $monthStart->format('Y-m'),
            'label' => $monthStart->format('F Y'),
            'student_joined' => $this->countPrepared(
                $pdo,
                'SELECT COUNT(*)
                 FROM students
                 WHERE joined_at >= :month_start
                   AND joined_at < :next_month_start',
                [
                    'month_start' => $monthStartSql,
                    'next_month_start' => $nextMonthStartSql,
                ]
            ),
            'confirmed_payments_amount' => $this->sumPrepared(
                $pdo,
                "SELECT COALESCE(SUM(amount), 0)
                 FROM payments
                 WHERE status = 'confirmed'
                   AND payment_date >= :month_start
                   AND payment_date < :next_month_start",
                [
                    'month_start' => $monthStartSql,
                    'next_month_start' => $nextMonthStartSql,
                ]
            ),
            'confirmed_payments_count' => $this->countPrepared(
                $pdo,
                "SELECT COUNT(*)
                 FROM payments
                 WHERE status = 'confirmed'
                   AND payment_date >= :month_start
                   AND payment_date < :next_month_start",
                [
                    'month_start' => $monthStartSql,
                    'next_month_start' => $nextMonthStartSql,
                ]
            ),
            'pending_payments_amount' => $this->sumPrepared(
                $pdo,
                "SELECT COALESCE(SUM(amount), 0)
                 FROM payments
                 WHERE status = 'pending'
                   AND payment_date >= :month_start
                   AND payment_date < :next_month_start",
                [
                    'month_start' => $monthStartSql,
                    'next_month_start' => $nextMonthStartSql,
                ]
            ),
            'pending_payments_count' => $this->countPrepared(
                $pdo,
                "SELECT COUNT(*)
                 FROM payments
                 WHERE status = 'pending'
                   AND payment_date >= :month_start
                   AND payment_date < :next_month_start",
                [
                    'month_start' => $monthStartSql,
                    'next_month_start' => $nextMonthStartSql,
                ]
            ),
            'import_batches' => $this->countPrepared(
                $pdo,
                'SELECT COUNT(*)
                 FROM statement_import_batches
                 WHERE created_at >= :month_start
                   AND created_at < :next_month_start',
                [
                    'month_start' => $monthStartSql,
                    'next_month_start' => $nextMonthStartSql,
                ]
            ),
            'import_rows' => $this->countPrepared(
                $pdo,
                'SELECT COUNT(*)
                 FROM statement_import_rows
                 WHERE created_at >= :month_start
                   AND created_at < :next_month_start',
                [
                    'month_start' => $monthStartSql,
                    'next_month_start' => $nextMonthStartSql,
                ]
            ),
            'import_queue_rows' => $this->countPrepared(
                $pdo,
                "SELECT COUNT(*)
                 FROM statement_import_rows
                 WHERE created_at >= :month_start
                   AND created_at < :next_month_start
                   AND status IN ('new', 'unmatched')",
                [
                    'month_start' => $monthStartSql,
                    'next_month_start' => $nextMonthStartSql,
                ]
            ),
            'draft_created_rows' => $this->countPrepared(
                $pdo,
                "SELECT COUNT(*)
                 FROM statement_import_rows
                 WHERE created_at >= :month_start
                   AND created_at < :next_month_start
                   AND status = 'draft_created'",
                [
                    'month_start' => $monthStartSql,
                    'next_month_start' => $nextMonthStartSql,
                ]
            ),
            'posted_expenses_amount' => $this->sumPrepared(
                $pdo,
                "SELECT COALESCE(SUM(amount), 0)
                 FROM expenses
                 WHERE status = 'posted'
                   AND expense_date >= :month_start
                   AND expense_date < :next_month_start",
                [
                    'month_start' => $monthStartSql,
                    'next_month_start' => $nextMonthStartSql,
                ]
            ),
            'posted_expenses_count' => $this->countPrepared(
                $pdo,
                "SELECT COUNT(*)
                 FROM expenses
                 WHERE status = 'posted'
                   AND expense_date >= :month_start
                   AND expense_date < :next_month_start",
                [
                    'month_start' => $monthStartSql,
                    'next_month_start' => $nextMonthStartSql,
                ]
            ),
            'draft_payouts_amount' => $this->sumPrepared(
                $pdo,
                "SELECT COALESCE(SUM(amount), 0)
                 FROM staff_payouts
                 WHERE status = 'draft'
                   AND payout_date >= :month_start
                   AND payout_date < :next_month_start",
                [
                    'month_start' => $monthStartSql,
                    'next_month_start' => $nextMonthStartSql,
                ]
            ),
            'posted_payouts_amount' => $this->sumPrepared(
                $pdo,
                "SELECT COALESCE(SUM(amount), 0)
                 FROM staff_payouts
                 WHERE status = 'posted'
                   AND payout_date >= :month_start
                   AND payout_date < :next_month_start",
                [
                    'month_start' => $monthStartSql,
                    'next_month_start' => $nextMonthStartSql,
                ]
            ),
            'posted_journal_entries' => $this->countPrepared(
                $pdo,
                "SELECT COUNT(*)
                 FROM journal_entries
                 WHERE status = 'posted'
                   AND entry_date >= :month_start
                   AND entry_date < :next_month_start",
                [
                    'month_start' => $monthStartSql,
                    'next_month_start' => $nextMonthStartSql,
                ]
            ),
            'operational_net_flow' => $this->sumPrepared(
                $pdo,
                "SELECT COALESCE(SUM(amount), 0)
                 FROM payments
                 WHERE status = 'confirmed'
                   AND payment_date >= :month_start
                   AND payment_date < :next_month_start",
                [
                    'month_start' => $monthStartSql,
                    'next_month_start' => $nextMonthStartSql,
                ]
            )
                - $this->sumPrepared(
                    $pdo,
                    "SELECT COALESCE(SUM(amount), 0)
                     FROM expenses
                     WHERE status = 'posted'
                       AND expense_date >= :month_start
                       AND expense_date < :next_month_start",
                    [
                        'month_start' => $monthStartSql,
                        'next_month_start' => $nextMonthStartSql,
                    ]
                )
                - $this->sumPrepared(
                    $pdo,
                    "SELECT COALESCE(SUM(amount), 0)
                     FROM staff_payouts
                     WHERE status = 'posted'
                       AND payout_date >= :month_start
                       AND payout_date < :next_month_start",
                    [
                        'month_start' => $monthStartSql,
                        'next_month_start' => $nextMonthStartSql,
                    ]
                ),
        ];
    }

    private function studentsByPlan(PDO $pdo): array
    {
        $statement = $pdo->query(
            "SELECT
                p.id,
                p.name,
                p.is_assignable,
                COUNT(s.id) AS total_students,
                COALESCE(SUM(CASE WHEN s.status = 'active' THEN 1 ELSE 0 END), 0) AS active_students
             FROM plans p
             LEFT JOIN students s ON s.plan_id = p.id
             GROUP BY p.id
             ORDER BY total_students DESC, p.name ASC, p.id ASC
             LIMIT 8"
        );

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    private function studentsByTeacher(PDO $pdo): array
    {
        $statement = $pdo->query(
            "SELECT
                st.id,
                st.first_name,
                st.family_name,
                st.father_name,
                st.status,
                COUNT(s.id) AS total_students,
                COALESCE(SUM(CASE WHEN s.status = 'active' THEN 1 ELSE 0 END), 0) AS active_students,
                COALESCE(SUM(CASE WHEN s.status = 'paused' THEN 1 ELSE 0 END), 0) AS paused_students,
                COALESCE(SUM(CASE WHEN s.status = 'archived' THEN 1 ELSE 0 END), 0) AS archived_students
             FROM staff st
             LEFT JOIN students s ON s.staff_id = st.id
             GROUP BY st.id
             ORDER BY active_students DESC, total_students DESC, st.first_name ASC, st.id ASC
             LIMIT 8"
        );

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    private function recentStatementBatches(PDO $pdo): array
    {
        $statement = $pdo->query(
            "SELECT
                b.id,
                b.original_filename,
                b.total_rows,
                b.new_rows,
                b.duplicate_rows,
                b.created_at,
                COALESCE(SUM(CASE WHEN r.status IN ('new', 'unmatched') THEN 1 ELSE 0 END), 0) AS open_rows
             FROM statement_import_batches b
             LEFT JOIN statement_import_rows r ON r.batch_id = b.id
             GROUP BY b.id
             ORDER BY b.created_at DESC, b.id DESC
             LIMIT 5"
        );

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    private function studentStatusCounts(int $staffId): array
    {
        $statement = Database::connection()->prepare(
            'SELECT status, COUNT(*) AS total
             FROM students
             WHERE staff_id = :staff_id
             GROUP BY status'
        );

        $statement->execute(['staff_id' => $staffId]);
        $counts = [
            'active' => 0,
            'paused' => 0,
            'archived' => 0,
        ];

        foreach ($statement->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $status = (string) ($row['status'] ?? '');
            if (array_key_exists($status, $counts)) {
                $counts[$status] = (int) ($row['total'] ?? 0);
            }
        }

        return $counts;
    }

    private function recentStudents(int $staffId): array
    {
        $statement = Database::connection()->prepare(
            'SELECT
                s.id,
                s.first_name,
                s.family_name,
                s.father_name,
                s.status,
                s.joined_at,
                p.name AS plan_name
             FROM students s
             INNER JOIN plans p ON p.id = s.plan_id
             WHERE s.staff_id = :staff_id
             ORDER BY s.joined_at DESC, s.id DESC
             LIMIT 5'
        );

        $statement->execute(['staff_id' => $staffId]);

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    private function topContributors(int $staffId, string $nextMonthStart): array
    {
        $statement = Database::connection()->prepare(
            "SELECT
                s.id,
                s.first_name,
                s.family_name,
                s.father_name,
                p.name AS plan_name,
                p.lesson_count * p.teacher_share_per_lesson AS monthly_teacher_share
             FROM students s
             INNER JOIN plans p ON p.id = s.plan_id
             WHERE s.staff_id = :staff_id
               AND s.status = 'active'
               AND s.joined_at < :next_month_start
             ORDER BY p.lesson_count * p.teacher_share_per_lesson DESC, s.joined_at ASC, s.id ASC
             LIMIT 5"
        );

        $statement->execute([
            'staff_id' => $staffId,
            'next_month_start' => $nextMonthStart,
        ]);

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    private function recentPayouts(int $staffId): array
    {
        $statement = Database::connection()->prepare(
            'SELECT id, payout_date, amount, status, posted_at
             FROM staff_payouts
             WHERE staff_id = :staff_id
             ORDER BY payout_date DESC, id DESC
             LIMIT 5'
        );

        $statement->execute(['staff_id' => $staffId]);

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }
}
