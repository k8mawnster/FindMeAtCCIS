<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('claims', function (Blueprint $table) {
            $table->dateTime('pickup_schedule')->nullable()->after('claim_status');
            $table->string('pickup_location', 255)->nullable()->after('pickup_schedule');
            $table->text('pickup_notes')->nullable()->after('pickup_location');
        });
    }

    public function down(): void {
        Schema::table('claims', function (Blueprint $table) {
            $table->dropColumn(['pickup_schedule', 'pickup_location', 'pickup_notes']);
        });
    }
};
