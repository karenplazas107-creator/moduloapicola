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
        if (Schema::hasTable('apiario')) {
            Schema::table('apiario', function (Blueprint $table) {
                if (!Schema::hasColumn('apiario', 'responsable')) {
                    $table->string('responsable', 150)->nullable()->default('Sergio Barrera')->after('nombre_apiario');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('apiario')) {
            Schema::table('apiario', function (Blueprint $table) {
                if (Schema::hasColumn('apiario', 'responsable')) {
                    $table->dropColumn('responsable');
                }
            });
        }
    }
};
