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
        Schema::create('lpj_pemasukan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lpj_id')->constrained('lpj')->onDelete('cascade');
            $table->foreignId('kategori_pemasukan_id')->constrained('kategori_pemasukan')->onDelete('cascade');
            $table->string('nama');
            $table->text('detail')->nullable();
            $table->bigInteger('nominal_rencana');
            $table->bigInteger('nominal_realisasi');
            $table->text('keterangan_realisasi')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lpj_pemasukan');
    }
};
