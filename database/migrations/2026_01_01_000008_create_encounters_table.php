<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('encounters', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('parish_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('movement_id')->constrained()->restrictOnDelete();
            $table->foreignUuid('responsible_user_id')->constrained('users')->restrictOnDelete();
            $table->string('name');
            $table->integer('edition_number')->nullable();
            $table->date('date');
            $table->string('location')->nullable();
            $table->string('status')->default('draft'); // EncounterStatus enum
            $table->timestamps();
            $table->softDeletes();

            $table->index(['parish_id', 'status']);
            $table->index(['parish_id', 'movement_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('encounters');
    }
};
