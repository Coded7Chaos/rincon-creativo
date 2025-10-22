<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('pedidos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre_cliente', 255);
            $table->string('telefono_cliente', 20);
            $table->text('direccion_envio');
            $table->string('departamento_envio', 100);
            $table->decimal('monto_total', 10, 2);
            $table->enum('estado', [
                'Pendiente', 
                'Pagado', 
                'Pendiente de Envío', 
                'Enviado', 
                'Completado', 
                'Cancelado'
            ])->default('Pendiente');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('pedidos');
    }
};