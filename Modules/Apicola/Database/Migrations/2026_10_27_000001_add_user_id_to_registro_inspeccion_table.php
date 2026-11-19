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
        if (Schema::hasTable('registro_inspeccion') && !Schema::hasColumn('registro_inspeccion', 'user_id')) {
            Schema::table('registro_inspeccion', function (Blueprint $table) {
                $table->foreignId('user_id')->nullable()->after('id_aprendiz')->constrained('users')->nullOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('registro_inspeccion') && Schema::hasColumn('registro_inspeccion', 'user_id')) {
            Schema::table('registro_inspeccion', function (Blueprint $table) {
                $table->dropForeign(['user_id']);
                $table->dropColumn('user_id');
            });
        }
    }
};
