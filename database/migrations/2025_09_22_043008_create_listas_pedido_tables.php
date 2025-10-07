<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        if (!Schema::hasTable('listas_pedido')) {
            Schema::create('listas_pedido', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id'); // quien creó
                $table->enum('status', ['borrador','enviada','cerrada','cancelada'])->default('borrador');
                $table->string('comentario', 255)->nullable();
                $table->timestamps();

                $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete()->cascadeOnUpdate();
                $table->index('status', 'idx_lp_status');
            });
        }

        if (!Schema::hasTable('lista_pedido_items')) {
            Schema::create('lista_pedido_items', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('lista_pedido_id');
                $table->unsignedBigInteger('producto_id');
                $table->unsignedInteger('cantidad');                // ENTERAS
                $table->unsignedBigInteger('proveedor_id')->nullable();
                $table->decimal('precio_estimado', 12, 2)->nullable();
                $table->timestamps();

                $table->foreign('lista_pedido_id')->references('id')->on('listas_pedido')->cascadeOnDelete()->cascadeOnUpdate();
                $table->foreign('producto_id')->references('id')->on('productos')->cascadeOnDelete()->cascadeOnUpdate();
                $table->foreign('proveedor_id')->references('id')->on('proveedors')->nullOnDelete()->cascadeOnUpdate();
            });
        }
    }
    public function down(): void {
        Schema::dropIfExists('lista_pedido_items');
        Schema::dropIfExists('listas_pedido');
    }
};
