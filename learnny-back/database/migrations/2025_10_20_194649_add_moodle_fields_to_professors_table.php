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
        // Assumindo que o nome da tabela é 'professors'
        Schema::table('professors', function (Blueprint $table) {
            
            // 1. Adiciona a coluna 'username'
            // O seu ProfessorCreateRequest já valida 'username', 
            // então este campo é esperado.
            if (!Schema::hasColumn('professors', 'username')) {
                $table->string('username')->unique()->after('name'); // ou depois do 'email'
            }

            // 2. Adiciona a coluna 'moodle_id'
            // Essencial para o MoodleService salvar a referência
            if (!Schema::hasColumn('professors', 'moodle_id')) {
                $table->unsignedBigInteger('moodle_id')->nullable()->unique()->after('id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('professors', function (Blueprint $table) {
            if (Schema::hasColumn('professors', 'moodle_id')) {
                $table->dropColumn('moodle_id');
            }
            if (Schema::hasColumn('professors', 'username')) {
                $table->dropColumn('username');
            }
        });
    }
};