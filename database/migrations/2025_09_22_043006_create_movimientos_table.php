<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('movimientos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('producto_id')->constrained('productos')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete()->cascadeOnUpdate(); // quién registró
            $table->enum('tipo', ['entrada','salida']);
            $table->unsignedInteger('cantidad');                 // piezas enteras
            $table->text('descripcion')->nullable();

            // Costos (entradas)
            $table->foreignId('proveedor_id')->nullable()->constrained('proveedors')->nullOnDelete()->cascadeOnUpdate();
            $table->decimal('costo_unitario', 12, 4)->nullable();
            $table->decimal('costo_total',   14, 4)->nullable();

            // Estado / confirmación
            $table->enum('status', ['pendiente','confirmado','cancelado'])->default('confirmado');
            $table->foreignId('confirmado_por')->nullable()->constrained('users')->nullOnDelete()->cascadeOnUpdate();
            $table->timestamp('confirmado_en')->nullable();

            // Auditoría de stock
            $table->unsignedInteger('existencias_despues');

            $table->timestamps();

            $table->index('tipo', 'idx_mov_tipo');
            $table->index('status', 'idx_mov_status');
            $table->index('created_at', 'idx_mov_created_at');
        });
    }

    public function down(): void {
        Schema::dropIfExists('movimientos');
    }
};
