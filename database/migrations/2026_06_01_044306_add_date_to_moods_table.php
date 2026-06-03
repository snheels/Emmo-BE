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
        Schema::table('moods', function (Blueprint $table) {
            $table->date('date')->after('mood');
        });
    }

    public function down(): void
    {
        Schema::table('moods', function (Blueprint $table) {
            $table->dropColumn('date');
        });
    }
};
