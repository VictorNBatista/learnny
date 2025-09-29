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
        Schema::create('availabilities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('professor_id')->constrained('professors')->onDelete('cascade');
            
            // Usaremos um inteiro para o dia da semana para facilitar as buscas
            // 0 = Domingo, 1 = Segunda-feira, 2 = Terça-feira, ..., 6 = Sábado
            $table->tinyInteger('day_of_week');

            // Apenas a hora de início e fim, pois o dia da semana já define a recorrência
            $table->time('start_time'); // Ex: 09:00:00
            $table->time('end_time');   // Ex: 17:00:00

            $table->timestamps();

            // Adiciona uma restrição para evitar que um professor cadastre
            // o mesmo dia da semana múltiplas vezes.
            $table->unique(['professor_id', 'day_of_week']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('availabilities');
    }
};
