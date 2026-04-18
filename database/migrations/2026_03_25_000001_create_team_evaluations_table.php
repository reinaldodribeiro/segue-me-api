<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('team_evaluations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('team_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('encounter_id')->constrained()->cascadeOnDelete();
            $table->uuid('token')->unique();
            $table->char('pin', 4);
            $table->string('status')->default('pending');
            $table->timestamp('expires_at')->nullable();

            // General team questions
            $table->tinyInteger('preparation_rating')->nullable();
            $table->text('preparation_comment')->nullable();
            $table->tinyInteger('teamwork_rating')->nullable();
            $table->text('teamwork_comment')->nullable();
            $table->tinyInteger('materials_rating')->nullable();
            $table->text('materials_comment')->nullable();
            $table->text('issues_text')->nullable();
            $table->text('improvements_text')->nullable();
            $table->tinyInteger('overall_team_rating')->nullable();

            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('team_evaluations');
    }
};
