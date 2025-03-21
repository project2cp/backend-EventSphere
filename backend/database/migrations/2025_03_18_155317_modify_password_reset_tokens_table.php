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
        Schema::table('password_reset_tokens', function (Blueprint $table) {
            $table->dropPrimary(); // Supprime la clé primaire sur "email"
            $table->index('email'); // Ajoute un index normal sur "email"
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('password_reset_tokens', function (Blueprint $table) {
            $table->dropIndex(['email']); // Supprime l'index ajouté
            $table->primary('email'); // Rétablit la clé primaire si on annule la migration
        });
    }
};
