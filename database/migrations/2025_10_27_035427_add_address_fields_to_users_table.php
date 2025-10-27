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
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone',8)->after('password');
            $table->string('departamento')->nullable()->after('phone');
            $table->string('city')->nullable()->after('departamento');
            $table->text('address')->nullable()->after('city');
            $table->boolean('is_active')->default(true)->after('address');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'phone',
                'departamento',
                'city',
                'address',
                'is_active',
            ]);
        });
    }
};
