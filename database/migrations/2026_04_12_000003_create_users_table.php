<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('user_id');
            $table->char('student_id', 9)->unique();
            $table->string('full_name', 100);
            $table->string('email', 100)->nullable()->unique();
            $table->string('phone_number', 15)->nullable();
            $table->string('password_hash', 255);
            $table->unsignedInteger('course_id')->nullable();
            $table->string('section_name', 20)->nullable();
            $table->enum('user_role', ['Student', 'Admin'])->default('Student');
            $table->enum('user_status', ['Active', 'Archived'])->default('Active');
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('course_id')
                  ->references('course_id')->on('courses')
                  ->nullOnDelete();
        });
    }

    public function down(): void {
        Schema::dropIfExists('users');
    }
};