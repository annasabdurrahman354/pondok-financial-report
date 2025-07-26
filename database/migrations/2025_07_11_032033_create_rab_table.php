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
        Schema::create('rab', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pondok_id')->constrained('pondok')->onDelete('cascade');
            $table->string('periode_id');
            $table->foreign('periode_id')->references('id')->on('periode')->onDelete('cascade');
            $table->enum('status', ['draft', 'diajukan', 'diterima', 'revisi'])->default('draft');
            $table->timestamp('accepted_at')->nullable();
            $table->text('pesan_revisi')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rab');
    }
};
