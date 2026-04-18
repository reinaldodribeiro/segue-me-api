<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('person_team_experiences', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('person_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('movement_team_id')->nullable()->constrained()->nullOnDelete();
            $table->string('team_name'); // stored denormalized so it survives team deletion
            $table->string('role')->default('member'); // 'coordinator' | 'member'
            $table->smallInteger('year')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('person_team_experiences');
    }
};
