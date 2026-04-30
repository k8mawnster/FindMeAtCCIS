<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('courses', function (Blueprint $table) {
            $table->increments('course_id');
            $table->string('course_code', 10)->unique();
            $table->string('course_name', 100);
        });

        DB::table('courses')->insert([
            ['course_code' => 'BSIT', 'course_name' => 'Bachelor of Science in Information Technology'],
            ['course_code' => 'BSCS', 'course_name' => 'Bachelor of Science in Computer Science'],
        ]);
    }

    public function down(): void {
        Schema::dropIfExists('courses');
    }
};