<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('team_members', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('team_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('person_id')->constrained()->cascadeOnDelete();
            $table->uuid('replaced_by_id')->nullable();
            $table->string('status')->default('pending'); // TeamMemberStatus enum
            $table->text('refusal_reason')->nullable();
            $table->timestamp('invited_at')->nullable();
            $table->timestamp('responded_at')->nullable();
            $table->timestamps();

            $table->unique(['team_id', 'person_id']);
            $table->index(['person_id', 'status']);
        });

        // FK auto-referencial adicionada após criação da tabela
        Schema::table('team_members', function (Blueprint $table) {
            $table->foreign('replaced_by_id')->references('id')->on('team_members')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('team_members');
    }
};
