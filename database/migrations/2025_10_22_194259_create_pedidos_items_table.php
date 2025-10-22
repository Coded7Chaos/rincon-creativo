<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('pedidos_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pedido_id')->constrained('pedidos')->onDelete('cascade');
            $table->foreignId('producto_id')->constrained('productos')->onDelete('restrict');
            $table->integer('cantidad');
            $table->decimal('precio_unitario', 10, 2);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('pedidos_items');
    }
};