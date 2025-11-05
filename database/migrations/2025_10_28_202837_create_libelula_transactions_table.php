<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('libelula_transactions', function (Blueprint $table) {
            $table->id();

            // Llave foránea a tu tabla de pedidos principal
            $table->foreignId('order_id')->constrained('orders');

            // Llave foránea al usuario (opcional, pero útil)
            $table->foreignId('user_id')->constrained('users');

            // Estado de esta transacción específica
            $table->string('status')->default('PENDIENTE'); // PENDIENTE, PAGADO, FALLIDO

            $table->decimal('monto', 10, 2); // Guardamos el monto de esta transacción

            // ID que NOSOTROS enviamos a Libélula (ej. LTXN-123)
            $table->string('identificador_deuda')->nullable()->unique();

            // ID que LIBÉLULA nos devuelve (ej. lib_abc...)
            $table->string('libelula_trans_id')->nullable()->index();

            // URL de la factura del webhook
            $table->string('factura_url')->nullable(); 

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('libelula_transactions');
    }
};
