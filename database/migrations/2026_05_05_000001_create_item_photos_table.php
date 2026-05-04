<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('item_photos', function (Blueprint $table) {
            $table->increments('photo_id');
            $table->unsignedInteger('item_id');
            $table->string('image_url', 255);
            $table->dateTime('created_at')->useCurrent();

            $table->foreign('item_id')->references('item_id')->on('items')->cascadeOnDelete();
        });
    }

    public function down(): void {
        Schema::dropIfExists('item_photos');
    }
};
