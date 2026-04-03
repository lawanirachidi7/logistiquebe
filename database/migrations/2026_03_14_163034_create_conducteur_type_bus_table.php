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
        Schema::create('conducteur_type_bus', function (Blueprint $table) {
            $table->unsignedBigInteger('conducteur_id');
            $table->unsignedBigInteger('type_bus_id');
            $table->foreign('conducteur_id')->references('id')->on('conducteurs')->onDelete('cascade');
            $table->foreign('type_bus_id')->references('id')->on('types_bus')->onDelete('cascade');
            $table->primary(['conducteur_id', 'type_bus_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conducteur_type_bus');
    }
};
