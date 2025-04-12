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
        Schema::create('offres', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recruteur_id')->constrained('recruteurs');
            $table->foreignId('entreprise_id')->constrained('entreprises');
            $table->string('titre');
            $table->text('description');
            $table->json('competences_requises')->nullable();
            $table->date('date_publication');
            $table->date('date_limite');
            $table->json('salaire')->nullable(); // Stores min/max as JSON
            $table->string('type_contrat');
            $table->string('lieu_travail');
            $table->string('niveau_etudes_requis')->nullable();
            $table->string('experience_requise')->nullable();
            $table->integer('nombre_vues')->default(0);
            $table->string('statut')->default('active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('offres');
    }
};
