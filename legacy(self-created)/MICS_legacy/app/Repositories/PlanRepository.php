<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Database;
use PDO;

final class PlanRepository
{
    public function findForList(?string $search, ?string $assignable): array
    {
        $sql = <<<'SQL'
            SELECT
                p.id,
                p.name,
                p.lesson_count,
                p.lesson_price,
                p.teacher_share_per_lesson,
                p.is_assignable,
                p.comments,
                p.created_at,
                COALESCE(student_usage.total_students, 0) AS total_students
            FROM plans p
            LEFT JOIN (
                SELECT plan_id, COUNT(*) AS total_students
                FROM students
                GROUP BY plan_id
            ) AS student_usage ON student_usage.plan_id = p.id
        SQL;

        $conditions = [];
        $params = [];

        if (is_string($assignable) && $assignable !== '') {
            $conditions[] = 'p.is_assignable = :is_assignable';
            $params['is_assignable'] = $assignable === 'assignable';
        }

        if (is_string($search) && trim($search) !== '') {
            $conditions[] = "(LOWER(p.name) LIKE :search OR LOWER(COALESCE(p.comments, '')) LIKE :search)";
            $params['search'] = '%'.strtolower(trim($search)).'%';
        }

        if ($conditions !== []) {
            $sql .= ' WHERE '.implode(' AND ', $conditions);
        }

        $sql .= ' ORDER BY p.is_assignable DESC, p.name ASC, p.id ASC';

        $statement = Database::connection()->prepare($sql);
        $statement->execute($params);

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findById(int $planId): ?array
    {
        $statement = Database::connection()->prepare(
            'SELECT
                id,
                name,
                lesson_count,
                lesson_price,
                teacher_share_per_lesson,
                is_assignable,
                comments
             FROM plans
             WHERE id = :id
             LIMIT 1'
        );

        $statement->execute(['id' => $planId]);
        $plan = $statement->fetch(PDO::FETCH_ASSOC);

        return is_array($plan) ? $plan : null;
    }

    public function create(array $data): int
    {
        $statement = Database::connection()->prepare(
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

        $statement->execute($this->planParams($data));

        return (int) $statement->fetchColumn();
    }

    public function update(int $planId, array $data): void
    {
        $params = $this->planParams($data);
        $params['id'] = $planId;

        $statement = Database::connection()->prepare(
            'UPDATE plans
             SET name = :name,
                 lesson_count = :lesson_count,
                 lesson_price = :lesson_price,
                 teacher_share_per_lesson = :teacher_share_per_lesson,
                 is_assignable = :is_assignable,
                 comments = :comments,
                 updated_at = CURRENT_TIMESTAMP
             WHERE id = :id'
        );

        $statement->execute($params);
    }

    public function nameExists(string $name, ?int $ignorePlanId = null): bool
    {
        $sql = 'SELECT 1 FROM plans WHERE LOWER(name) = LOWER(:name)';
        $params = ['name' => $name];

        if ($ignorePlanId !== null) {
            $sql .= ' AND id <> :ignore_id';
            $params['ignore_id'] = $ignorePlanId;
        }

        $sql .= ' LIMIT 1';

        $statement = Database::connection()->prepare($sql);
        $statement->execute($params);

        return $statement->fetchColumn() !== false;
    }

    private function planParams(array $data): array
    {
        return [
            'name' => $data['name'],
            'lesson_count' => $data['lesson_count'],
            'lesson_price' => $data['lesson_price'],
            'teacher_share_per_lesson' => $data['teacher_share_per_lesson'],
            'is_assignable' => $data['is_assignable'],
            'comments' => $data['comments'],
        ];
    }
}
