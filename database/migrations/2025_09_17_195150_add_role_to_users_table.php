<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
{
    Schema::table('users', function (Blueprint $table) {
        // enum: restringe a dos valores permitidos
        $table->enum('role', ['admin','empleado'])
              ->default('empleado') // por seguridad, todos entran como empleado
              ->after('email');     // organización: lo coloca después de email
    });
}

public function down(): void
{
    Schema::table('users', function (Blueprint $table) {
        $table->dropColumn('role');
    });
}
};
