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
        Schema::table('dify_workflows', function (Blueprint $table) {
            $table->enum('status', ['active', 'inactive', 'error', 'syncing'])->default('active')->after('is_active');
            $table->text('status_message')->nullable()->after('status');
            $table->timestamp('last_status_check')->nullable()->after('status_message');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dify_workflows', function (Blueprint $table) {
            $table->dropColumn(['status', 'status_message', 'last_status_check']);
        });
    }
};
