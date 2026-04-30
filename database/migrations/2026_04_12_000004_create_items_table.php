<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('items', function (Blueprint $table) {
            $table->increments('item_id');
            $table->string('name', 100);
            $table->text('description');
            $table->string('image_url', 255)->nullable();
            $table->dateTime('date_reported')->useCurrent();
            $table->string('last_known_location', 255)->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->enum('item_status', ['Lost', 'Found']);
            $table->enum('verification_status', ['Pending', 'Approved', 'Rejected'])->default('Pending');
            $table->string('rejection_reason', 255)->nullable();
            $table->unsignedInteger('reported_by_user_id');
            $table->unsignedInteger('category_id');

            $table->foreign('reported_by_user_id')
                  ->references('user_id')->on('users')
                  ->restrictOnDelete();
            $table->foreign('category_id')
                  ->references('category_id')->on('categories')
                  ->restrictOnDelete();
        });
    }

    public function down(): void {
        Schema::dropIfExists('items');
    }
};          