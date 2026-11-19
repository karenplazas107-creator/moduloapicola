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
        if (Schema::hasTable('apicola_colmenas')) {
            Schema::table('apicola_colmenas', function (Blueprint $table) {
                if (!Schema::hasColumn('apicola_colmenas', 'tipo_abeja')) {
                    $table->string('tipo_abeja', 100)->default('Apis mellifera')->after('responsable');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('apicola_colmenas')) {
            Schema::table('apicola_colmenas', function (Blueprint $table) {
                if (Schema::hasColumn('apicola_colmenas', 'tipo_abeja')) {
                    $table->dropColumn('tipo_abeja');
                }
            });
        }
    }
};
