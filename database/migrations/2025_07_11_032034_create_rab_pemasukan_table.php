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
        Schema::create('rab_pemasukan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rab_id')->constrained('rab')->onDelete('cascade');
            $table->foreignId('kategori_pemasukan_id')->constrained('kategori_pemasukan')->onDelete('cascade');
            $table->string('nama');
            $table->text('detail')->nullable();
            $table->bigInteger('nominal');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rab_pemasukan');
    }
};
