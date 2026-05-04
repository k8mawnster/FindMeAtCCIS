<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('categories', function (Blueprint $table) {
            $table->increments('category_id');
            $table->string('name', 50)->unique();
        });

        DB::table('categories')->insert([
            ['name' => 'ID Cards'],
            ['name' => 'Wallets'],
            ['name' => 'Electronics'],
            ['name' => 'Books'],
            ['name' => 'Keys'],
            ['name' => 'Clothing'],
            ['name' => 'Other'],
        ]);
    }

    public function down(): void {
        Schema::dropIfExists('categories');
    }
};
