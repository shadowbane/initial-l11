<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('activity_log', static function (Blueprint $table) {
            $table->after('description', static function (Blueprint $table) {
                $table->char('request_identifier', 26)
                    ->nullable()
                    ->index('activity_log_request_identifier_index');
                $table->string('ip')->nullable();
                $table->json('browser_detail')->nullable();
                $table->json('request_detail')->nullable();
            });
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('activity_log', static function (Blueprint $table) {
            $table->dropColumn(['request_identifier', 'ip', 'browser_detail', 'request_detail']);
        });
    }
};
