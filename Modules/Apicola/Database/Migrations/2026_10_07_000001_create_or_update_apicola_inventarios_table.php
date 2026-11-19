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
        if (!Schema::hasTable('apicola_inventarios')) {
            Schema::create('apicola_inventarios', function (Blueprint $table) {
                $table->id();
                $table->string('nombre', 150);
                $table->string('codigo', 50)->unique();
                $table->string('categoria', 100)->default('Herramienta');
                $table->string('bodega', 150)->default('Bodega central');
                $table->string('unidad_medida', 50)->default('unidad');
                $table->integer('cantidad')->default(0);
                $table->integer('stock_minimo')->default(0);
                $table->string('estado', 50)->default('Activo');
                $table->string('responsable', 150)->nullable();
                $table->text('notas')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        } else {
            Schema::table('apicola_inventarios', function (Blueprint $table) {
                if (!Schema::hasColumn('apicola_inventarios', 'bodega')) {
                    $table->string('bodega', 150)->default('Bodega central')->after('categoria');
                }
                if (!Schema::hasColumn('apicola_inventarios', 'unidad_medida')) {
                    $table->string('unidad_medida', 50)->default('unidad')->after('bodega');
                }
            });
        }

        if (!Schema::hasTable('apicola_inventario_movimientos')) {
            Schema::create('apicola_inventario_movimientos', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('inventario_id');
                $table->string('tipo', 50); // Entrada, Salida, Ajuste
                $table->integer('cantidad');
                $table->integer('stock_anterior')->default(0);
                $table->integer('stock_nuevo')->default(0);
                $table->string('motivo', 255)->nullable();
                $table->string('responsable', 150)->nullable();
                $table->dateTime('fecha')->nullable();
                $table->timestamps();

                $table->foreign('inventario_id')
                    ->references('id')
                    ->on('apicola_inventarios')
                    ->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('apicola_inventarios')) {
            Schema::table('apicola_inventarios', function (Blueprint $table) {
                if (Schema::hasColumn('apicola_inventarios', 'unidad_medida')) {
                    $table->dropColumn('unidad_medida');
                }
                if (Schema::hasColumn('apicola_inventarios', 'bodega')) {
                    $table->dropColumn('bodega');
                }
            });
        }
    }
};
