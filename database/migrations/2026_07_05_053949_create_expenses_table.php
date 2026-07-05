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
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('expense_category_id')->constrained()->restrictOnDelete();
            $table->date('month_date')->nullable()->index();
            $table->decimal('amount', 10, 2);
            $table->string('status', 20)->default('draft')->index();
            $table->boolean('is_auto_generated')->default(false)->index();
            $table->text('note')->nullable();
            $table->timestamps();

            $table->index(['expense_category_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
