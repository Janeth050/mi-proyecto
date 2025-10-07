<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        if (!Schema::hasTable('categorias')) {
            Schema::create('categorias', function (Blueprint $table) {
                $table->id();
                $table->string('nombre', 120)->unique();
                $table->softDeletes();
                $table->timestamps();
                $table->index('deleted_at', 'idx_categorias_deleted_at');
            });
        }
    }
    public function down(): void {
        Schema::dropIfExists('categorias');
    }
};
