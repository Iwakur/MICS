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
        Schema::create('student_months', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->date('month_date');
            $table->decimal('opening_balance', 10, 2)->default(0);
            $table->decimal('charge_amount', 10, 2)->default(0);
            $table->decimal('manual_adjustment', 10, 2)->default(0);
            $table->text('note')->nullable();
            $table->timestamps();

            $table->unique(['student_id', 'month_date']);
            $table->index('month_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_months');
    }
};
