<?php

declare(strict_types=1);

require dirname(__DIR__).'/app/init.php';

use App\Database;
use App\DatabaseProvisioner;
use App\Services\RecurringFinanceService;

$payloadPath = $argv[1] ?? base_path('outputs/migration_workbook/migration_payload.json');
$payloadJson = is_file($payloadPath) ? file_get_contents($payloadPath) : false;

if ($payloadJson === false) {
    throw new RuntimeException('Migration payload file not found: '.$payloadPath);
}

$payload = json_decode($payloadJson, true, 512, JSON_THROW_ON_ERROR);

DatabaseProvisioner::rebuildPublicSchema();

$pdo = Database::connection();
$migrationTimestamp = current_app_datetime()->format('Y-m-d H:i:sP');
$staffIdByKey = [];
$planIdByName = [];

$normalizeBoolean = static function (mixed $value, bool $default = false): bool {
    if (is_bool($value)) {
        return $value;
    }

    if ($value === null) {
        return $default;
    }

    if (is_int($value) || is_float($value)) {
        return (bool) $value;
    }

    if (is_string($value)) {
        $normalized = strtolower(trim($value));

        if ($normalized === '') {
            return $default;
        }

        if (in_array($normalized, ['1', 'true', 'yes', 'y'], true)) {
            return true;
        }

        if (in_array($normalized, ['0', 'false', 'no', 'n'], true)) {
            return false;
        }
    }

    return $default;
};

$normalizeBooleanSql = static function (mixed $value, bool $default = false) use ($normalizeBoolean): string {
    return $normalizeBoolean($value, $default) ? 'true' : 'false';
};

try {
    $pdo->beginTransaction();

    $insertStaff = $pdo->prepare(
        'INSERT INTO staff (
            role,
            first_name,
            family_name,
            father_name,
            status,
            payout_card_number,
            fixed_salary_amount,
            phone,
            email,
            comments,
            created_at,
            updated_at
         ) VALUES (
            :role,
            :first_name,
            :family_name,
            :father_name,
            :status,
            :payout_card_number,
            :fixed_salary_amount,
            NULL,
            NULL,
            :comments,
            CURRENT_TIMESTAMP,
            CURRENT_TIMESTAMP
         )
         RETURNING id'
    );

    foreach ($payload['staff'] as $staff) {
        $comments = trim(implode(' | ', array_filter([
            $staff['comments'] ?? null,
            ! empty($staff['aliases']) ? 'Aliases: '.implode(', ', $staff['aliases']) : null,
        ], static fn ($value): bool => is_string($value) && $value !== '')));

        $insertStaff->execute([
            'role' => $staff['role'],
            'first_name' => $staff['first_name'],
            'family_name' => $staff['family_name'],
            'father_name' => $staff['father_name'],
            'status' => $staff['status'] ?? 'active',
            'payout_card_number' => $staff['payout_card_number'],
            'fixed_salary_amount' => $staff['fixed_salary_amount'],
            'comments' => $comments !== '' ? $comments : null,
        ]);

        $staffIdByKey[(string) $staff['key']] = (int) $insertStaff->fetchColumn();
    }

    $insertPlan = $pdo->prepare(
        'INSERT INTO plans (
            name,
            lesson_count,
            lesson_price,
            teacher_share_per_lesson,
            is_assignable,
            comments,
            created_at,
            updated_at
         ) VALUES (
            :name,
            :lesson_count,
            :lesson_price,
            :teacher_share_per_lesson,
            :is_assignable,
            :comments,
            CURRENT_TIMESTAMP,
            CURRENT_TIMESTAMP
         )
         RETURNING id'
    );

    foreach ($payload['plans'] as $plan) {
        $insertPlan->execute([
            'name' => $plan['name'],
            'lesson_count' => $plan['lesson_count'],
            'lesson_price' => $plan['lesson_price'],
            'teacher_share_per_lesson' => $plan['teacher_share_per_lesson'],
            'is_assignable' => $normalizeBooleanSql($plan['is_assignable'] ?? null),
            'comments' => $plan['comments'],
        ]);

        $planIdByName[(string) $plan['name']] = (int) $insertPlan->fetchColumn();
    }

    $insertStudent = $pdo->prepare(
        'INSERT INTO students (
            first_name,
            family_name,
            father_name,
            phone,
            email,
            status,
            plan_id,
            staff_id,
            discount_amount,
            joined_at,
            comments,
            created_at,
            updated_at
         ) VALUES (
            :first_name,
            :family_name,
            :father_name,
            NULL,
            NULL,
            :status,
            :plan_id,
            :staff_id,
            :discount_amount,
            :joined_at,
            NULL,
            CURRENT_TIMESTAMP,
            CURRENT_TIMESTAMP
         )'
    );

    foreach ($payload['students'] as $student) {
        $staffId = $staffIdByKey[(string) $student['staff_key']] ?? null;
        $planId = $planIdByName[(string) $student['plan_name']] ?? null;

        if ($staffId === null) {
            throw new RuntimeException('Missing staff mapping for student row: '.(string) ($student['raw_name'] ?? 'unknown'));
        }

        if ($planId === null) {
            throw new RuntimeException('Missing plan mapping for student row: '.(string) ($student['raw_name'] ?? 'unknown'));
        }

        $insertStudent->execute([
            'first_name' => $student['first_name'],
            'family_name' => $student['family_name'],
            'father_name' => $student['father_name'],
            'status' => $student['status'],
            'plan_id' => $planId,
            'staff_id' => $staffId,
            'discount_amount' => $student['discount_amount'],
            'joined_at' => $student['joined_at'] === 'MIGRATION_TIMESTAMP' ? $migrationTimestamp : $student['joined_at'],
        ]);
    }

    $teacherStaffKey = (string) ($payload['default_teacher_user_staff_key'] ?? '');
    if ($teacherStaffKey !== '' && isset($staffIdByKey[$teacherStaffKey])) {
        $teacherStaffId = $staffIdByKey[$teacherStaffKey];
        $teacherUser = $pdo->prepare('SELECT id FROM users WHERE username = :username LIMIT 1');
        $teacherUser->execute(['username' => 'teacher']);
        $existingTeacherUserId = $teacherUser->fetchColumn();

        if ($existingTeacherUserId === false) {
            $insertTeacherUser = $pdo->prepare(
                'INSERT INTO users (staff_id, username, password_hash, role, is_active, created_at, updated_at)
                 VALUES (:staff_id, :username, :password_hash, :role, TRUE, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)'
            );

            $insertTeacherUser->execute([
                'staff_id' => $teacherStaffId,
                'username' => 'teacher',
                'password_hash' => password_hash(default_user_password(), PASSWORD_DEFAULT),
                'role' => 'teacher',
            ]);
        } else {
            $updateTeacherUser = $pdo->prepare(
                'UPDATE users
                 SET staff_id = :staff_id,
                     role = :role,
                     is_active = TRUE,
                     updated_at = CURRENT_TIMESTAMP
                 WHERE id = :id'
            );

            $updateTeacherUser->execute([
                'id' => $existingTeacherUserId,
                'staff_id' => $teacherStaffId,
                'role' => 'teacher',
            ]);
        }
    }

    $pdo->commit();
} catch (Throwable $throwable) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    throw $throwable;
}

(new RecurringFinanceService)->ensureCurrentMonthDocuments();

$counts = [];
foreach (['staff', 'plans', 'students', 'student_charges', 'staff_payouts', 'payments', 'expenses'] as $table) {
    $counts[$table] = (int) $pdo->query('SELECT COUNT(*) FROM '.$table)->fetchColumn();
}

echo "Rebuild and migration complete.\n";
echo 'Payload: '.$payloadPath."\n";
foreach ($counts as $table => $count) {
    echo strtoupper($table).': '.$count."\n";
}
