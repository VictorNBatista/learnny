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
        Schema::table('professors', function (Blueprint $table) {
            // Define a coluna 'status' como um enum com os valores permitidos
            $table->enum('status', ['pending', 'approved', 'rejected'])
                  ->default('pending') // Define 'pending' como o valor padrão
                  ->after('price'); // Coloca a coluna logo após a coluna 'price'
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('professors', function (Blueprint $table) {
            // Remove a coluna 'status' se a migration for revertida
            $table->dropColumn('status');
        });
    }
};
