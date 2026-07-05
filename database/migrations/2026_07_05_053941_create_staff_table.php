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
            $table->decimal('salary_amount', 10, 2)->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('staff');
    }
};
