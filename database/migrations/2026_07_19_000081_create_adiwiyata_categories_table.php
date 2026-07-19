<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('adiwiyata_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('weight', 5, 2)->default(0);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        $categories = [
            ['name' => 'Kebersihan Lingkungan', 'description' => 'Pengelolaan kebersihan lingkungan sekolah', 'weight' => 17, 'sort_order' => 1],
            ['name' => 'Pengelolaan Sampah', 'description' => 'Pemilahan dan pengolahan sampah', 'weight' => 17, 'sort_order' => 2],
            ['name' => 'Konservasi Air', 'description' => 'Penghematan dan daur ulang air', 'weight' => 17, 'sort_order' => 3],
            ['name' => 'Konservasi Energi', 'description' => 'Efisiensi dan hemat energi', 'weight' => 17, 'sort_order' => 4],
            ['name' => 'Keanekaragaman Hayati', 'description' => 'Pelestarian flora dan fauna', 'weight' => 16, 'sort_order' => 5],
            ['name' => 'Inovasi Lingkungan', 'description' => 'Inovasi dan kreativitas peduli lingkungan', 'weight' => 16, 'sort_order' => 6],
        ];

        foreach ($categories as $cat) {
            DB::table('adiwiyata_categories')->insert(array_merge($cat, ['created_at' => now(), 'updated_at' => now()]));
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('adiwiyata_categories');
    }
};
