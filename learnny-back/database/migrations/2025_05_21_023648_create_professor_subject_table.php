<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('professor_subject', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('professor_id');
            $table->unsignedBigInteger('subject_id');
            $table->timestamps();

            // Chaves estrangeiras
            $table->foreign('professor_id')->references('id')->on('professors')->onDelete('cascade');
            $table->foreign('subject_id')->references('id')->on('subjects')->onDelete('cascade');

            // Evita duplicidade
            $table->unique(['professor_id', 'subject_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('professor_subject');
    }
};