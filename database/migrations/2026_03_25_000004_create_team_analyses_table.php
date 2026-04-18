<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('team_analyses', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('encounter_analysis_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('team_id')->constrained()->cascadeOnDelete();
            $table->text('analysis');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('team_analyses');
    }
};
