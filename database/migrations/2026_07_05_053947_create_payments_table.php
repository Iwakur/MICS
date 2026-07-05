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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_month_id')->constrained()->cascadeOnDelete();
            $table->timestamp('paid_at')->index();
            $table->decimal('amount', 10, 2);
            $table->string('payment_method', 50);
            $table->string('status', 20)->default('draft')->index();
            $table->text('note')->nullable();
            $table->timestamps();

            $table->index(['student_month_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
