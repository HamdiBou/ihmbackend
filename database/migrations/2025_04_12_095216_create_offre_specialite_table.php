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
        Schema::create('offre_specialite', function (Blueprint $table) {
            $table->foreignId('offre_id')->constrained();
            $table->foreignId('specialite_id')->constrained();
            $table->primary(['offre_id', 'specialite_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('offre_specialite');
    }
};
