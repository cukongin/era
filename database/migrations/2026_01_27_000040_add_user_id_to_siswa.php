<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('siswa', function (Blueprint $table) {
            $table->foreignId('id_user')->nullable()->after('id')->constrained('users')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('siswa', function (Blueprint $table) {
             $table->dropForeign(['id_user']);
             $table->dropColumn('id_user');
        });
    }
};
