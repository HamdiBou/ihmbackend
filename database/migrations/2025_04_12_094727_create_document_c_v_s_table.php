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
        Schema::create('document_cvs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('chercheur_emploi_id');
            $table->string('nom_fichier');
            $table->string('type_fichier');
            $table->bigInteger('taille');
            $table->date('date_import');
            $table->string('chemin_stockage');
            $table->text('extracted_text')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('chercheur_emploi_id')->references('id')->on('chercheurs_emploi')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_c_v_s');
    }
};
