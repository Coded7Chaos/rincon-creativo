<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Punto 2a: Añadir índice al estado de la orden
        Schema::table('orders', function (Blueprint $table) {
            $table->index('state');
        });

        // Puntos 2a y 2c: Añadir índice y columnas de auditoría a las transacciones
        Schema::table('libelula_transactions', function (Blueprint $table) {
            // (Tu BD ya tiene índices en 'identificador_deuda' y 'libelula_trans_id')

            // Índice para buscar por estado (PENDIENTE, PAGADO, etc)
            $table->index('status');

            // Columnas de auditoría (para depuración)
            $table->string('pasarela_url')->nullable()->after('factura_url');
            $table->json('payload_snapshot')->nullable()->after('pasarela_url');
            $table->decimal('amount_sent', 12, 2)->nullable()->after('payload_snapshot');
            $table->string('currency', 3)->default('BOB')->after('amount_sent');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['state']);
        });

        Schema::table('libelula_transactions', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropColumn([
                'pasarela_url', 
                'payload_snapshot', 
                'amount_sent', 
                'currency'
            ]);
        });
    }
};