<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teams', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('encounter_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('movement_team_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->integer('min_members')->default(1);
            $table->integer('max_members');
            $table->string('accepted_type')->default('all'); // TeamAcceptedType enum
            $table->json('recommended_skills')->default('[]');
            $table->integer('order')->default(0);
            $table->timestamps();

            $table->index('encounter_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teams');
    }
};
