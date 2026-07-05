<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Create monthly snapshots, reviewable cash activity, and audit history.
     *
     * Debt is derived from student_months and validated payments. Generated
     * salaries retain source rows so later catalog edits cannot rewrite history.
     */
    public function up(): void
    {
        Schema::create('student_months', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->date('month_date');
            $table->unsignedInteger('lesson_count')->default(0);
            $table->decimal('opening_balance', 10, 2)->default(0);
            $table->decimal('charge_amount', 10, 2)->default(0);
            $table->decimal('manual_adjustment', 10, 2)->default(0);
            $table->string('status', 20)->default('draft')->index();
            $table->foreignId('validated_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('validated_at')->nullable();
            $table->text('adjustment_reason')->nullable();
            $table->foreignId('adjusted_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('note')->nullable();
            $table->timestamps();

            $table->unique(['student_id', 'month_date']);
            $table->index('month_date');
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            // A unique self-reference permits one immutable full reversal per payment.
            $table->foreignId('reversal_of_payment_id')->nullable()->unique()
                ->constrained('payments')->restrictOnDelete();
            $table->foreignId('student_month_id')->constrained()->cascadeOnDelete();
            $table->timestamp('paid_at')->index();
            $table->decimal('amount', 10, 2);
            $table->string('payment_method', 50);
            $table->string('status', 20)->default('draft')->index();
            $table->foreignId('validated_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('validated_at')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();

            $table->index(['student_month_id', 'status']);
        });

        Schema::create('expense_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();
            $table->text('note')->nullable();
            $table->timestamps();
        });

        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            // Staff deletion never removes financial history.
            $table->foreignId('staff_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('expense_category_id')->constrained()->restrictOnDelete();
            $table->date('month_date')->nullable()->index();
            $table->decimal('amount', 10, 2);
            $table->string('status', 20)->default('draft')->index();
            $table->foreignId('validated_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('validated_at')->nullable();
            $table->boolean('is_auto_generated')->default(false)->index();
            $table->string('generation_key')->nullable()->unique();
            $table->text('note')->nullable();
            $table->timestamps();

            $table->index(['expense_category_id', 'status']);
        });

        Schema::create('salary_draft_sources', function (Blueprint $table) {
            $table->id();
            $table->foreignId('expense_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->nullable()->constrained()->nullOnDelete();
            $table->string('source_type', 30)->index();
            $table->string('description');
            $table->decimal('units', 10, 2)->default(1);
            $table->decimal('rate', 10, 2);
            $table->decimal('amount', 10, 2);
            $table->timestamps();

            $table->index(['expense_id', 'source_type']);
        });

        Schema::create('billing_months', function (Blueprint $table) {
            $table->id();
            $table->date('month_date')->unique();
            $table->string('status', 20)->default('open')->index();
            $table->foreignId('closed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('billing_month_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('billing_month_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action', 20);
            $table->text('reason')->nullable();
            $table->timestamp('occurred_at');
            $table->timestamps();

            $table->index(['billing_month_id', 'occurred_at']);
        });

        Schema::create('bank_months', function (Blueprint $table) {
            $table->id();
            $table->date('month_date')->unique();
            $table->decimal('opening_balance', 10, 2)->default(0);
            $table->decimal('closing_balance', 10, 2)->default(0);
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('billing_month_events');
        Schema::dropIfExists('billing_months');
        Schema::dropIfExists('salary_draft_sources');
        Schema::dropIfExists('expenses');
        Schema::dropIfExists('expense_categories');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('student_months');
        Schema::dropIfExists('bank_months');
    }
};
