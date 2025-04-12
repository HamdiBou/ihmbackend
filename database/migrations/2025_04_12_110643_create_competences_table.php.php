<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('competences', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->timestamps();
        });

        Schema::create('profil_competence', function (Blueprint $table) {
            $table->unsignedBigInteger('profil_id');
            $table->unsignedBigInteger('competence_id');
            $table->primary(['profil_id', 'competence_id']);

            $table->foreign('profil_id')->references('id')->on('profil_professionnels')->onDelete('cascade');
            $table->foreign('competence_id')->references('id')->on('competences')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('profil_competence');
        Schema::dropIfExists('competences');
    }
};;
