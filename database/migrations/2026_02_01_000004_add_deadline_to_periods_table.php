<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('periode', function (Blueprint $table) {
            $table->dateTime('end_date')->nullable()->after('status');
        });
    }

    public function down()
    {
        Schema::table('periode', function (Blueprint $table) {
            $table->dropColumn('end_date');
        });
    }
};
