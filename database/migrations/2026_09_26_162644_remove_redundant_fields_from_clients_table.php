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
    Schema::table('clients', function (Blueprint $table) {
        $table->dropColumn(['tempat_kejadian', 'jenis_kelamin', 'umur', 'alamat']);
    });
}

public function down(): void
{
    Schema::table('clients', function (Blueprint $table) {
        $table->string('tempat_kejadian')->nullable();
        $table->enum('jenis_kelamin', ['L', 'P'])->nullable();
        $table->integer('umur')->nullable();
        $table->text('alamat')->nullable();
    });
}
};
