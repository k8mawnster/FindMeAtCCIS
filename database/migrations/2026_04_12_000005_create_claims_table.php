<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('claims', function (Blueprint $table) {
            $table->increments('claim_id');
            $table->unsignedInteger('item_id');
            $table->unsignedInteger('claimed_by_user_id');
            $table->string('claimer_full_name', 100);
            $table->string('claimer_email', 100)->nullable();
            $table->string('claimer_course_section', 50)->nullable();
            $table->dateTime('claim_date')->useCurrent();
            $table->text('proof_description');
            $table->string('proof_photo_url', 255)->nullable();
            $table->enum('claim_status', ['Pending', 'Under Review', 'Verified', 'Rejected', 'Resolved'])->default('Pending');

            $table->foreign('item_id')->references('item_id')->on('items')->cascadeOnDelete();
            $table->foreign('claimed_by_user_id')->references('user_id')->on('users')->restrictOnDelete();
        });
    }

    public function down(): void {
        Schema::dropIfExists('claims');
    }
};