<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('webhook_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_execution_id')->constrained()->onDelete('cascade');
            $table->string('webhook_url');
            $table->json('payload');
            $table->integer('http_status')->nullable();
            $table->text('response_body')->nullable();
            $table->text('error_message')->nullable();
            $table->decimal('response_time_ms', 8, 2)->nullable();
            $table->integer('attempt_number')->default(1);
            $table->enum('status', ['pending', 'success', 'failed'])->default('pending');
            $table->timestamp('attempted_at');
            $table->timestamps();

            $table->index(['task_execution_id', 'attempt_number']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('webhook_attempts');
    }
};