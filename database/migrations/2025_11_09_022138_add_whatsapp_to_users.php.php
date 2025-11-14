<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'whatsapp_phone')) {
                $table->string('whatsapp_phone', 20)->nullable()->after('email');
            }
            if (!Schema::hasColumn('users', 'notify_low_stock')) {
                $table->boolean('notify_low_stock')->default(true)->after('whatsapp_phone');
            }
        });
    }

    public function down(): void {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'notify_low_stock')) {
                $table->dropColumn('notify_low_stock');
            }
            if (Schema::hasColumn('users', 'whatsapp_phone')) {
                $table->dropColumn('whatsapp_phone');
            }
        });
    }
};
