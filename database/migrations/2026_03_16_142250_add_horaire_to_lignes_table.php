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
            $table->time('horaire')->default('06:00:00')->after('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lignes', function (Blueprint $table) {
            $table->dropColumn('horaire');
        });
    }
};
