<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('letter_types', function (Blueprint $table) {
            $table->boolean('kop_enabled')->default(true)->after('kop_footer_enabled');
        });
    }

    public function down(): void
    {
        Schema::table('letter_types', function (Blueprint $table) {
            $table->dropColumn('kop_enabled');
        });
    }
};
