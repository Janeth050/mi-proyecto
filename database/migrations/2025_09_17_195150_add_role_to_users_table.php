<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        if (!Schema::hasColumn('users', 'role')) {
            Schema::table('users', function (Blueprint $table) {
                $table->enum('role', ['admin','empleado'])->default('empleado')->after('password');
                $table->index('role', 'idx_users_role');
            });
        }
    }
    public function down(): void {
        if (Schema::hasColumn('users', 'role')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropIndex('idx_users_role');
                $table->dropColumn('role');
            });
        }
    }
};

