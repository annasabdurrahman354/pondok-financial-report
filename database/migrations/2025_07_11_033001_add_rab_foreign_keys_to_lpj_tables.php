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
        Schema::table('lpj_pemasukan', function (Blueprint $table) {
            $table->unsignedBigInteger('rab_pemasukan_id')->nullable()->after('lpj_id');
            $table->foreign('rab_pemasukan_id')->references('id')->on('rab_pemasukan')->onDelete('set null');
        });

        Schema::table('lpj_pengeluaran', function (Blueprint $table) {
            $table->unsignedBigInteger('rab_pengeluaran_id')->nullable()->after('lpj_id');
            $table->foreign('rab_pengeluaran_id')->references('id')->on('rab_pengeluaran')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lpj_pemasukan', function (Blueprint $table) {
            $table->dropForeign(['rab_pemasukan_id']);
            $table->dropColumn('rab_pemasukan_id');
        });

        Schema::table('lpj_pengeluaran', function (Blueprint $table) {
            $table->dropForeign(['rab_pengeluaran_id']);
            $table->dropColumn('rab_pengeluaran_id');
        });
    }
};

