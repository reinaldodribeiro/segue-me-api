<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('encounter_participants', function (Blueprint $table) {
            $table->string('photo')->nullable()->after('partner_birth_date');
        });
    }

    public function down(): void
    {
        Schema::table('encounter_participants', function (Blueprint $table) {
            $table->dropColumn('photo');
        });
    }
};
