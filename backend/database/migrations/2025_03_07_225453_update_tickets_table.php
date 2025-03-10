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
        Schema::table('tickets', function (Blueprint $table) {
            $table->string('ticket_number')->unique()->after('user_id');
            $table->string('qr_code')->nullable()->after('ticket_number');
            $table->boolean('is_paid')->default(false)->after('qr_code');
            $table->enum('status', ['confirmed', 'cancelled', 'refunded'])->default('confirmed')->after('is_paid');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropColumn(['ticket_number', 'qr_code', 'is_paid', 'status']);
        });
    }
};
