<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('voyages', function (Blueprint $table) {
            $table->boolean('force_nuit')->default(false)->after('periode');
        });
    }

    public function down()
    {
        Schema::table('voyages', function (Blueprint $table) {
            $table->dropColumn('force_nuit');
        });
    }
};
