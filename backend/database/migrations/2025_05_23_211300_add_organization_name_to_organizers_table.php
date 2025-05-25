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
        Schema::table('organizers', function (Blueprint $table) {
            $table->string('organization_name') // Champ pour le nom de l'organisation
                  ->after('user_id')            // Optionnel : positionnement
                  ->nullable();                 // Ou ->default('Nom par défaut')
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('organizers', function (Blueprint $table) {
            $table->dropColumn('organization_name');
        });
    }
};
