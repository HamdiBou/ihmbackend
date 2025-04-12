<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('offres', function (Blueprint $table) {
            $table->id();
            $table->string('titre');
            $table->text('description');
            $table->date('date_publication');
            $table->date('date_limite');
            $table->decimal('salaire_min', 10, 2)->nullable();
            $table->decimal('salaire_max', 10, 2)->nullable();
            $table->string('type_contrat');
            $table->string('lieu_travail');
            $table->string('niveau_etudes_requis')->nullable();
            $table->string('experience_requise')->nullable();
            $table->unsignedBigInteger('entreprise_id');
            $table->unsignedBigInteger('recruteur_id');
            $table->integer('nombre_vues')->default(0);
            $table->string('statut')->default('active');
            $table->timestamps();

            $table->foreign('entreprise_id')->references('id')->on('entreprises')->onDelete('cascade');
            $table->foreign('recruteur_id')->references('id')->on('recruteurs')->onDelete('cascade');
        });

        Schema::create('offre_specialite', function (Blueprint $table) {
            $table->unsignedBigInteger('offre_id');
            $table->unsignedBigInteger('specialite_id');
            $table->primary(['offre_id', 'specialite_id']);

            $table->foreign('offre_id')->references('id')->on('offres')->onDelete('cascade');
            $table->foreign('specialite_id')->references('id')->on('specialites')->onDelete('cascade');
        });

        Schema::create('offre_competence', function (Blueprint $table) {
            $table->unsignedBigInteger('offre_id');
            $table->unsignedBigInteger('competence_id');
            $table->primary(['offre_id', 'competence_id']);

            $table->foreign('offre_id')->references('id')->on('offres')->onDelete('cascade');
            $table->foreign('competence_id')->references('id')->on('competences')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('offre_competence');
        Schema::dropIfExists('offre_specialite');
        Schema::dropIfExists('offres');
    }
};
