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
        Schema::create('workflow_stream_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_execution_id')->constrained()->onDelete('cascade');
            $table->string('event_type'); // workflow_started, node_started, text_chunk, node_finished, workflow_finished, etc.
            $table->string('task_id')->nullable(); // from streaming response
            $table->string('workflow_run_id')->nullable(); // from streaming response  
            $table->string('node_id')->nullable(); // for node events
            $table->json('event_data'); // full event data as JSON
            $table->timestamp('event_timestamp'); // when the event occurred
            $table->timestamps();
            
            $table->index(['task_execution_id', 'event_type']);
            $table->index(['workflow_run_id']);
            $table->index(['event_timestamp']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workflow_stream_events');
    }
};
