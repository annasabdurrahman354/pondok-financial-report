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
        Schema::create('pengurus_pondok', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pondok_id')->constrained('pondok')->onDelete('cascade');
            $table->string('nama');
            $table->string('nomor_telepon');
            $table->string('jabatan');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengurus_pondok');
    }
};
