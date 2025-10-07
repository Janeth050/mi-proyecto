<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('kardex', function (Blueprint $table) {
            $table->id();
            $table->foreignId('producto_id')->constrained('productos')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('movimiento_id')->constrained('movimientos')->cascadeOnDelete()->cascadeOnUpdate();
            $table->timestamp('fecha');  // now() al registrar
            $table->enum('tipo', ['entrada','salida']);
            $table->unsignedInteger('entrada')->default(0);
            $table->unsignedInteger('salida')->default(0);
            $table->unsignedInteger('saldo')->default(0);
            $table->decimal('costo_unitario', 12, 4)->nullable();
            $table->decimal('costo_total',   14, 4)->nullable();
            $table->timestamps();

            $table->index(['producto_id','fecha'], 'idx_kardex_prod_fecha');
        });
    }

    public function down(): void {
        Schema::dropIfExists('kardex');
    }
};
