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
        Schema::create('pondok', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('status')->default('reguler');
            $table->string('nomor_telepon')->nullable();
            $table->text('alamat_lengkap')->nullable();
            $table->string('provinsi')->nullable();
            $table->string('kota')->nullable();
            $table->string('kecamatan')->nullable();
            $table->string('kelurahan')->nullable();
            $table->string('kode_pos')->nullable();
            $table->string('daerah_sambung')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pondok');
    }
};
