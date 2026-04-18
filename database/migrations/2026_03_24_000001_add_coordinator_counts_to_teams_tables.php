<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('movement_teams', function (Blueprint $table) {
            $table->integer('coordinators_youth')->default(0)->after('max_members');
            $table->integer('coordinators_couples')->default(0)->after('coordinators_youth');
        });

        Schema::table('teams', function (Blueprint $table) {
            $table->integer('coordinators_youth')->default(0)->after('max_members');
            $table->integer('coordinators_couples')->default(0)->after('coordinators_youth');
        });
    }

    public function down(): void
    {
        Schema::table('movement_teams', function (Blueprint $table) {
            $table->dropColumn(['coordinators_youth', 'coordinators_couples']);
        });

        Schema::table('teams', function (Blueprint $table) {
            $table->dropColumn(['coordinators_youth', 'coordinators_couples']);
        });
    }
};
