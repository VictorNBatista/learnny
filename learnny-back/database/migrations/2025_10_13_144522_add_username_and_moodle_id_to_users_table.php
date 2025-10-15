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
            // Adiciona a coluna 'username' após a coluna 'name'
            // É única para evitar duplicatas.
            $table->string('username')->unique()->after('name');

            // Adiciona a coluna 'moodle_id'.
            // É 'unsignedBigInteger' para corresponder ao tipo de ID padrão do Laravel.
            // É 'nullable' porque o usuário pode ser criado no Learnny antes de ser sincronizado com o Moodle.
            // É 'unique' porque um usuário do Moodle só pode estar ligado a um usuário do Learnny.
            $table->unsignedBigInteger('moodle_id')->nullable()->unique()->after('id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Remove as colunas na ordem inversa da criação
            $table->dropColumn('moodle_id');
            $table->dropColumn('username');
        });
    }
};