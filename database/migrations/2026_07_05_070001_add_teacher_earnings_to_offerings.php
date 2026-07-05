<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lesson_types', function (Blueprint $table) {
            $table->decimal('teacher_share_per_lesson', 10, 2)->default(0);
        });

        Schema::table('plans', function (Blueprint $table) {
            $table->decimal('teacher_monthly_amount', 10, 2)->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('lesson_types', function (Blueprint $table) {
            $table->dropColumn('teacher_share_per_lesson');
        });

        Schema::table('plans', function (Blueprint $table) {
            $table->dropColumn('teacher_monthly_amount');
        });
    }
};
