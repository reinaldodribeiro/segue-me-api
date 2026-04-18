<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('movements', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('parish_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('target_audience'); // PersonType enum + 'all'
            $table->string('scope');            // MovementScope enum
            $table->text('description')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['parish_id', 'active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('movements');
    }
};
