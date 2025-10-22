<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('transacciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pedido_id')->constrained('pedidos')->onDelete('cascade');
            $table->string('libelula_deuda_id', 255);
            $table->enum('estado_pago', [
                'Pendiente',
                'Confirmado', 
                'Fallido', 
                'Rechazado'
            ])->default('Pendiente');
            $table->decimal('monto', 10, 2);
            $table->string('metodo_pago', 50)->nullable();
            $table->json('datos_callback')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('transacciones');
    }
};