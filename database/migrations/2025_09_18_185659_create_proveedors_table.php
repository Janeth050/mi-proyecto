<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        if (!Schema::hasTable('proveedors')) {
            Schema::create('proveedors', function (Blueprint $table) {
                $table->id();
                $table->string('nombre')->unique();
                $table->string('telefono', 50)->nullable();
                $table->string('correo', 255)->nullable();
                $table->string('direccion', 255)->nullable();
                $table->text('notas')->nullable();
                $table->softDeletes();
                $table->timestamps();
                $table->index('deleted_at', 'idx_prov_deleted_at');
            });
        }
    }
    public function down(): void {
        Schema::dropIfExists('proveedors');
    }
};

