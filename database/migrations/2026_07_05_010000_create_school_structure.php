<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Create business identities, teaching catalogs, and student assignments.
     *
     * Login access remains in users while organizational roles remain in
     * staff_roles. Catalog records are archived instead of deleted once used.
     */
    public function up(): void
    {
        Schema::create('staff_roles', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();
            $table->boolean('can_teach')->default(false)->index();
            $table->boolean('is_active')->default(true)->index();
            $table->text('note')->nullable();
            $table->timestamps();
        });

        Schema::create('staff', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_role_id')->constrained()->restrictOnDelete();
            $table->string('first_name', 100);
            $table->string('family_name', 100)->nullable();
            $table->string('father_name', 100)->nullable();
            $table->string('email')->nullable()->index();
            $table->string('phone', 32)->nullable()->index();
            $table->date('birthday')->nullable();
            $table->string('city', 100)->nullable();
            $table->string('payout_card_number', 64)->nullable();
            $table->string('compensation_mode', 20)->default('fixed')->index();
            $table->decimal('salary_amount', 10, 2)->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->text('note')->nullable();
            $table->timestamps();
        });

        // Accounts may be created before linking, but one account can link to only one staff record.
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('staff_id')->nullable()->unique()->constrained()->nullOnDelete();
            $table->timestamp('last_login_at')->nullable()->index();
        });

        Schema::create('lesson_types', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();
            $table->unsignedInteger('duration_minutes');
            $table->decimal('lesson_price', 10, 2);
            $table->decimal('teacher_share_per_lesson', 10, 2)->default(0);
            $table->boolean('is_assignable')->default(true)->index();
            $table->text('note')->nullable();
            $table->timestamps();
        });

        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();
            $table->unsignedInteger('duration_minutes');
            $table->unsignedInteger('lesson_count');
            $table->decimal('lesson_price', 10, 2);
            $table->decimal('plan_price', 10, 2);
            $table->decimal('teacher_monthly_amount', 10, 2)->default(0);
            $table->boolean('is_assignable')->default(true)->index();
            $table->text('note')->nullable();
            $table->timestamps();
        });

        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_id')->constrained()->restrictOnDelete();
            $table->string('first_name', 100);
            $table->string('family_name', 100)->nullable();
            $table->string('father_name', 100)->nullable();
            $table->string('email')->nullable()->index();
            $table->string('phone', 32)->nullable()->index();
            $table->date('birthday')->nullable();
            $table->string('city', 100)->nullable();
            $table->date('joined_at')->index();
            $table->string('status', 20)->default('active')->index();
            $table->string('billing_type', 20)->index();
            $table->foreignId('lesson_type_id')->nullable()->constrained()->restrictOnDelete();
            $table->unsignedInteger('lesson_amount')->nullable();
            $table->foreignId('plan_id')->nullable()->constrained()->restrictOnDelete();
            $table->date('plan_start_at')->nullable();
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->text('note')->nullable();
            $table->timestamps();

            $table->index(['staff_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
        Schema::dropIfExists('plans');
        Schema::dropIfExists('lesson_types');

        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('staff_id');
            $table->dropColumn('last_login_at');
        });

        Schema::dropIfExists('staff');
        Schema::dropIfExists('staff_roles');
    }
};
