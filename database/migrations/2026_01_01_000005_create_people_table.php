<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('people', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('parish_id')->constrained()->cascadeOnDelete();
            $table->string('type'); // PersonType enum: youth|couple
            $table->string('name');
            $table->string('partner_name')->nullable();
            $table->string('photo')->nullable();
            $table->date('birth_date')->nullable();
            $table->date('partner_birth_date')->nullable();
            $table->date('wedding_date')->nullable();
            $table->string('email')->nullable();
            // jsonb no PostgreSQL para suporte a GIN index; json no SQLite
            $table->addColumn(DB::getDriverName() === 'pgsql' ? 'jsonb' : 'json', 'skills')->default('[]');
            $table->text('notes')->nullable();
            $table->integer('engagement_score')->default(0);
            $table->boolean('active')->default(true);
            $table->unsignedSmallInteger('encounter_year')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Common new fields
            $table->string('nickname', 100)->nullable();
            $table->text('address')->nullable();
            $table->string('birthplace', 255)->nullable();
            $table->json('phones')->nullable();
            $table->text('church_movement')->nullable();
            $table->date('received_at')->nullable();
            $table->text('encounter_details')->nullable();

            // Youth-only fields
            $table->string('father_name', 255)->nullable();
            $table->string('mother_name', 255)->nullable();
            $table->string('education_level', 100)->nullable();
            $table->string('education_status', 50)->nullable();
            $table->string('course', 255)->nullable();
            $table->string('institution', 255)->nullable();
            $table->json('sacraments')->nullable();
            $table->text('available_schedule')->nullable();
            $table->text('musical_instruments')->nullable();
            $table->text('talks_testimony')->nullable();

            // Couple-only fields
            $table->string('partner_nickname', 100)->nullable();
            $table->string('partner_birthplace', 255)->nullable();
            $table->string('partner_email', 255)->nullable();
            $table->json('partner_phones')->nullable();
            $table->string('partner_photo', 255)->nullable();
            $table->json('home_phones')->nullable();

            $table->index(['parish_id', 'active']);
            $table->index('engagement_score');
        });

        // GIN index for JSONB skills search in PostgreSQL (skipped on SQLite)
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('CREATE INDEX people_skills_gin_idx ON people USING GIN (skills)');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('people');
    }
};
