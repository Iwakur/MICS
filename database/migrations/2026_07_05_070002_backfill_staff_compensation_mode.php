<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('staff')
            ->whereNull('salary_amount')
            ->update(['compensation_mode' => 'dynamic']);
    }

    public function down(): void
    {
        DB::table('staff')
            ->where('compensation_mode', 'dynamic')
            ->update(['compensation_mode' => 'fixed']);
    }
};
