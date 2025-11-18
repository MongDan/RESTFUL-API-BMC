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
        Schema::table('persalinan', function (Blueprint $table) {
            // Hanya foreign key pasien_no_reg
            $table->foreign('pasien_no_reg', 'fk_persalinan_pasien')
                  ->references('no_reg')->on('pasien')
                  ->onUpdate('no action')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('persalinan', function (Blueprint $table) {
            $table->dropForeign('fk_persalinan_pasien');
        });
    }
};
