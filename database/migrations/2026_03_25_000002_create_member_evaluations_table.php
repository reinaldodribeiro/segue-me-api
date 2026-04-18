<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('member_evaluations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('team_evaluation_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('team_member_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('person_id')->constrained()->cascadeOnDelete();

            $table->tinyInteger('commitment_rating');
            $table->string('fulfilled_responsibilities'); // yes, partially, no
            $table->text('positive_highlight')->nullable();
            $table->text('issue_observed')->nullable();
            $table->string('recommend'); // yes, with_reservations, no

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('member_evaluations');
    }
};
