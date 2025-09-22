<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
   {
        Schema::create('proveedors', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');                 // Nombre del proveedor
            $table->string('telefono')->nullable();   // Tel de contacto
            $table->string('correo')->nullable();     // Correo de contacto
            $table->string('direccion')->nullable();  // Dirección (opcional)
            $table->text('notas')->nullable();        // Notas (horarios, condiciones, etc.)
            $table->timestamps();
       });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('proveedors');
    }
};
