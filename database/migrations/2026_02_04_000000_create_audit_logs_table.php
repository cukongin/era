<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAuditLogsTable extends Migration
{
    public function up()
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('user_name')->nullable(); // Snapshot of name in case user is deleted
            $table->string('action'); // e.g., 'LOGIN', 'UPDATE_NILAI', 'DELETE_SISWA'
            $table->string('target')->nullable(); // e.g., 'Siswa: Budi', 'Mapel: MTK'
            $table->text('details')->nullable(); // JSON or text description
            $table->string('ip_address')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('audit_logs');
    }
}
