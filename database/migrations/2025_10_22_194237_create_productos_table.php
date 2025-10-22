<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('productos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 255);
            $table->text('descripcion');
            $table->decimal('precio', 10, 2);
            $table->integer('stock')->default(0);
            $table->string('imagen_url', 255)->nullable();
            $table->foreignId('categoria_id')->nullable()->constrained('categorias')->onDelete('set null');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('productos');
    }
};