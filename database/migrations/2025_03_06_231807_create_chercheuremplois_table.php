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
        Schema::create('chercheurs_emploi', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('utilisateur_id');
            $table->string('niveau_etudes')->nullable();
            $table->integer('annees_experience')->nullable();
            $table->string('disponibilite')->nullable();
            $table->string('preference_emploi')->nullable();
            $table->decimal('salaire_attendu_min', 10, 2)->nullable();
            $table->decimal('salaire_attendu_max', 10, 2)->nullable();
            $table->string('mobilite')->nullable();
            $table->timestamps();

            $table->foreign('utilisateur_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chercheuremplois');
    }
};
