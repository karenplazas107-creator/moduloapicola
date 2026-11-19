<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('apicola_movimientos') && !Schema::hasColumn('apicola_movimientos', 'motivo')) {
            Schema::table('apicola_movimientos', function (Blueprint $table) {
                $table->string('motivo', 100)->nullable()->after('tipo');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('apicola_movimientos') && Schema::hasColumn('apicola_movimientos', 'motivo')) {
            Schema::table('apicola_movimientos', function (Blueprint $table) {
                $table->dropColumn('motivo');
            });
        }
    }
};
