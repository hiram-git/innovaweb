<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    /**
     * Vincula usuarios de Laravel con BASEUSUARIOS del ERP Clarion.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('erp_coduser', 50)
                ->nullable()
                ->unique()
                ->after('name')
                ->comment('CODUSER de BASEUSUARIOS en el ERP Clarion');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('erp_coduser');
        });
    }
};
