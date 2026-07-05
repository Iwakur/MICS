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
            $table->unsignedInteger('lesson_count')->default(0)->after('month_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_months', function (Blueprint $table) {
            $table->dropColumn('lesson_count');
        });
    }
};
