<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            $this->rebuildPlansTable(true);

            return;
        }

        DB::statement('ALTER TABLE plans ALTER COLUMN lesson_count TYPE NUMERIC(5,1) USING lesson_count::numeric(5,1)');
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            $this->rebuildPlansTable(false);

            return;
        }

        DB::statement('ALTER TABLE plans ALTER COLUMN lesson_count TYPE INTEGER USING ROUND(lesson_count)::integer');
    }

    private function rebuildPlansTable(bool $toDecimal): void
    {
        Schema::disableForeignKeyConstraints();

        try {
            Schema::dropIfExists('plans_new');
            DB::statement('DROP INDEX IF EXISTS plans_name_unique');
            DB::statement('DROP INDEX IF EXISTS plans_is_assignable_index');

            Schema::create('plans_new', function (Blueprint $table) use ($toDecimal): void {
                $table->id();
                $table->string('name', 100)->unique();
                $table->unsignedInteger('duration_minutes');
                $toDecimal
                    ? $table->decimal('lesson_count', 5, 1)
                    : $table->unsignedInteger('lesson_count');
                $table->decimal('lesson_price', 10, 2);
                $table->decimal('plan_price', 10, 2);
                $table->decimal('teacher_monthly_amount', 10, 2)->default(0);
                $table->boolean('is_assignable')->default(true)->index();
                $table->text('note')->nullable();
                $table->timestamps();
            });

            DB::statement(
                'INSERT INTO plans_new (id, name, duration_minutes, lesson_count, lesson_price, plan_price, teacher_monthly_amount, is_assignable, note, created_at, updated_at)
                 SELECT id, name, duration_minutes, lesson_count, lesson_price, plan_price, teacher_monthly_amount, is_assignable, note, created_at, updated_at
                 FROM plans'
            );

            Schema::drop('plans');
            Schema::rename('plans_new', 'plans');
        } finally {
            Schema::enableForeignKeyConstraints();
        }
    }
};
