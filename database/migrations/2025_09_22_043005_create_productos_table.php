<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('productos', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 64)->unique();
            $table->string('nombre', 255);
            $table->foreignId('unidad_id')->constrained('unidades')->restrictOnDelete()->cascadeOnUpdate();
            $table->foreignId('categoria_id')->nullable()->constrained('categorias')->nullOnDelete()->cascadeOnUpdate();
            $table->unsignedInteger('existencias')->default(0);          // piezas enteras
            $table->unsignedInteger('stock_minimo')->default(0);
            $table->string('presentacion_detalle', 120)->nullable();     // opcional (ej. "costal 25kg")
            $table->decimal('costo_promedio', 12, 4)->default(0);
            $table->softDeletes();
            $table->timestamps();

            $table->index('nombre', 'idx_prod_nombre');
        });
    }

    public function down(): void {
        Schema::dropIfExists('productos');
    }
};
