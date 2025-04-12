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
        Schema::create('profil_professionnels', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('chercheur_emploi_id');
            $table->string('titre');
            $table->text('resume_competences')->nullable();
            $table->boolean('extracted_from_cv')->default(false);
            $table->timestamps();

            $table->foreign('chercheur_emploi_id')->references('id')->on('chercheurs_emploi')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profil_professionnels');
    }
};
