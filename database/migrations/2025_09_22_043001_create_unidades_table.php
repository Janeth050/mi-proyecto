<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        if (!Schema::hasTable('unidades')) {
            Schema::create('unidades', function (Blueprint $table) {
                $table->id();
                $table->string('clave', 20)->unique();     // ej: costal, caja, pza
                $table->string('descripcion', 100);
                $table->timestamps();
            });
        }
    }
    public function down(): void {
        Schema::dropIfExists('unidades');
    }
};
