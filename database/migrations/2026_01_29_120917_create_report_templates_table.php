<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReportTemplatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('report_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g., "Standard Rapor", "Cover Kemenag"
            $table->string('type')->default('rapor'); // 'cover', 'rapor', 'ledger'
            $table->longText('content')->nullable(); // The HTML content
            $table->json('margins')->nullable(); // {top: 10, right: 10, ...}
            $table->string('orientation')->default('portrait'); // 'portrait', 'landscape'
            $table->boolean('is_active')->default(false);
            $table->boolean('is_locked')->default(false); // TRUE = Cannot Edit/Delete
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('report_templates');
    }
}
