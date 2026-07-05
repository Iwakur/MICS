<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
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

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
