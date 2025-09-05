<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('group')->index();
            $table->string('key');
            $table->json('value');
            $table->string('type')->default('string');
            $table->string('description')->nullable();
            $table->boolean('is_public')->default(false);
            $table->timestamps();
            
            $table->unique(['group', 'key']);
            $table->index('is_public');
        });
        
        // Seed default queue settings
        DB::table('settings')->insert([
            [
                'group' => 'queue',
                'key' => 'dify_max_jobs_per_minute',
                'value' => json_encode(10),
                'type' => 'integer',
                'description' => 'Maximum Dify workflow executions per minute',
                'is_public' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'group' => 'queue',
                'key' => 'dify_max_concurrent_jobs',
                'value' => json_encode(3),
                'type' => 'integer',
                'description' => 'Maximum simultaneous Dify executions',
                'is_public' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'group' => 'queue',
                'key' => 'dify_retry_after_seconds',
                'value' => json_encode(60),
                'type' => 'integer',
                'description' => 'Delay in seconds before retrying rate-limited jobs',
                'is_public' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};