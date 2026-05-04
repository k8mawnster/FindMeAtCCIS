<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void {
        $phoneId = DB::table('categories')->where('name', 'Phones')->value('category_id');
        $gadgetId = DB::table('categories')->where('name', 'Gadgets')->value('category_id');
        $electronicsId = DB::table('categories')->where('name', 'Electronics')->value('category_id');

        if (!$electronicsId) {
            if ($gadgetId) {
                DB::table('categories')
                    ->where('category_id', $gadgetId)
                    ->update(['name' => 'Electronics']);
                $electronicsId = $gadgetId;
            } else {
                $electronicsId = DB::table('categories')->insertGetId(['name' => 'Electronics']);
            }
        }

        if ($phoneId && $phoneId !== $electronicsId) {
            DB::table('items')
                ->where('category_id', $phoneId)
                ->update(['category_id' => $electronicsId]);

            DB::table('categories')
                ->where('category_id', $phoneId)
                ->delete();
        }
    }

    public function down(): void {
        $electronicsId = DB::table('categories')->where('name', 'Electronics')->value('category_id');

        DB::table('categories')->updateOrInsert(
            ['name' => 'Phones'],
            ['name' => 'Phones']
        );

        if ($electronicsId) {
            DB::table('categories')
                ->where('category_id', $electronicsId)
                ->update(['name' => 'Gadgets']);
        }
    }
};
