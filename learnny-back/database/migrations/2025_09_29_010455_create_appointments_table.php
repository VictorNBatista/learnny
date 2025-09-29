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
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();

            // Chaves estrangeiras para os relacionamentos
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('professor_id')->constrained('professors')->onDelete('cascade');
            $table->foreignId('subject_id')->constrained('subjects')->onDelete('cascade');

            // Detalhes do agendamento
            $table->dateTime('start_time');
            $table->dateTime('end_time');
            $table->decimal('price_paid', 8, 2);
            $table->text('location_details')->nullable(); // Para link de aula online ou endereço físico

            // Status do agendamento
            $table->enum('status', [
                'pending',              // Aguardando confirmação (do prof. ou do pagamento)
                'confirmed',            // Aula confirmada
                'completed',            // Aula finalizada
                'cancelled_by_user',    // Cancelado pelo aluno
                'cancelled_by_professor'// Cancelado pelo professor
            ])->default('pending');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
