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
        // 1. Tabla de comprobantes generales de movimiento
        if (!Schema::hasTable('apicola_movimientos')) {
            Schema::create('apicola_movimientos', function (Blueprint $table) {
                $table->id();
                $table->string('numero_comprobante', 50)->unique();
                $table->string('tipo', 50); // Entrada, Salida
                $table->string('bodega', 150)->default('Bodega Central');
                $table->date('fecha');
                $table->integer('total_productos')->default(0);
                $table->integer('total_unidades')->default(0);
                $table->string('responsable', 150)->nullable();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->text('observaciones')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        // 2. Tabla de detalles por producto dentro de cada comprobante
        if (!Schema::hasTable('apicola_movimiento_detalles')) {
            Schema::create('apicola_movimiento_detalles', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('movimiento_id');
                $table->unsignedBigInteger('inventario_id')->nullable();
                $table->string('producto_nombre', 150);
                $table->string('producto_codigo', 50)->nullable();
                $table->integer('cantidad')->default(1);
                $table->integer('stock_anterior')->default(0);
                $table->integer('stock_nuevo')->default(0);
                $table->string('observaciones', 255)->nullable();
                $table->timestamps();

                $table->foreign('movimiento_id')
                    ->references('id')
                    ->on('apicola_movimientos')
                    ->onDelete('cascade');

                if (Schema::hasTable('apicola_inventarios')) {
                    $table->foreign('inventario_id')
                        ->references('id')
                        ->on('apicola_inventarios')
                        ->onDelete('set null');
                }
            });
        }

        // 3. Vincular con movimientos históricos de inventario
        if (Schema::hasTable('apicola_inventario_movimientos')) {
            Schema::table('apicola_inventario_movimientos', function (Blueprint $table) {
                if (!Schema::hasColumn('apicola_inventario_movimientos', 'movimiento_id')) {
                    $table->unsignedBigInteger('movimiento_id')->nullable()->after('inventario_id');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('apicola_inventario_movimientos')) {
            Schema::table('apicola_inventario_movimientos', function (Blueprint $table) {
                if (Schema::hasColumn('apicola_inventario_movimientos', 'movimiento_id')) {
                    $table->dropColumn('movimiento_id');
                }
            });
        }

        Schema::dropIfExists('apicola_movimiento_detalles');
        Schema::dropIfExists('apicola_movimientos');
    }
};
