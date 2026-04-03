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
        Schema::table('lignes', function (Blueprint $table) {
            $table->foreignId('ligne_retour_id')->nullable()->after('type')
                ->constrained('lignes')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lignes', function (Blueprint $table) {
            $table->dropForeign(['ligne_retour_id']);
            $table->dropColumn('ligne_retour_id');
        });
    }
};
