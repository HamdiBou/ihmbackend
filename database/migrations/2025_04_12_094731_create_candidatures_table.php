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
        Schema::create('candidatures', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('chercheur_emploi_id');
            $table->unsignedBigInteger('offre_id');
            $table->unsignedBigInteger('document_cv_id');
            $table->unsignedBigInteger('lettre_motivation_id')->nullable();
            $table->date('date_postulation');
            $table->string('statut')->default('en_attente');
            $table->integer('note_recruteur')->nullable();
            $table->text('commentaire')->nullable();
            $table->timestamps();

            $table->foreign('chercheur_emploi_id')->references('id')->on('chercheurs_emploi')->onDelete('cascade');
            $table->foreign('offre_id')->references('id')->on('offres')->onDelete('cascade');
            $table->foreign('document_cv_id')->references('id')->on('document_cvs')->onDelete('cascade');
            $table->foreign('lettre_motivation_id')->references('id')->on('lettres_motivation')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('candidatures');
    }
};
