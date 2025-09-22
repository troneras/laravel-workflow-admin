<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Add job timeout setting
        DB::table('settings')->insert([
            'group' => 'queue',
            'key' => 'job_timeout_seconds',
            'value' => json_encode(14400), // 4 hours in seconds
            'type' => 'integer',
            'description' => 'Job execution timeout in seconds (default: 4 hours)',
            'is_public' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        DB::table('settings')
            ->where('group', 'queue')
            ->where('key', 'job_timeout_seconds')
            ->delete();
    }
};