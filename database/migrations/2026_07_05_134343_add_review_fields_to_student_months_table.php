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
        Schema::table('student_months', function (Blueprint $table) {
            $table->string('status', 20)->default('draft')->index()->after('manual_adjustment');
            $table->text('adjustment_reason')->nullable()->after('status');
            $table->foreignId('adjusted_by_user_id')->nullable()->after('adjustment_reason')->constrained('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_months', function (Blueprint $table) {
            $table->dropConstrainedForeignId('adjusted_by_user_id');
            $table->dropColumn(['status', 'adjustment_reason']);
        });
    }
};
