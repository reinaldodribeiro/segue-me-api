<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('team_evaluations', function (Blueprint $table) {
            $table->index(['encounter_id', 'status'], 'team_evaluations_encounter_id_status_index');
        });

        Schema::table('member_evaluations', function (Blueprint $table) {
            $table->index(['team_evaluation_id', 'person_id'], 'member_evaluations_team_evaluation_id_person_id_index');
            $table->index(['team_member_id'], 'member_evaluations_team_member_id_index');
        });

        Schema::table('ai_api_logs', function (Blueprint $table) {
            $table->index(['created_at', 'action', 'success'], 'ai_api_logs_created_at_action_success_index');
        });
    }

    public function down(): void
    {
        Schema::table('team_evaluations', function (Blueprint $table) {
            $table->dropIndex('team_evaluations_encounter_id_status_index');
        });

        Schema::table('member_evaluations', function (Blueprint $table) {
            $table->dropIndex('member_evaluations_team_evaluation_id_person_id_index');
            $table->dropIndex('member_evaluations_team_member_id_index');
        });

        Schema::table('ai_api_logs', function (Blueprint $table) {
            $table->dropIndex('ai_api_logs_created_at_action_success_index');
        });
    }
};
