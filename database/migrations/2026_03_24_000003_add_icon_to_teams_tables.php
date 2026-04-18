<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('movement_teams', function (Blueprint $table) {
            $table->string('icon')->nullable()->after('name');
        });

        Schema::table('teams', function (Blueprint $table) {
            $table->string('icon')->nullable()->after('name');
        });
    }

    public function down(): void
    {
        Schema::table('movement_teams', function (Blueprint $table) {
            $table->dropColumn('icon');
        });

        Schema::table('teams', function (Blueprint $table) {
            $table->dropColumn('icon');
        });
    }
};
