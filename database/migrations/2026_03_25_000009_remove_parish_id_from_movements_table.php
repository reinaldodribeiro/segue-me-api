<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('movements', function (Blueprint $table) {
            $table->dropIndex(['parish_id', 'active']);
            $table->dropForeign(['parish_id']);
            $table->dropColumn('parish_id');

            $table->index('active');
        });
    }

    public function down(): void
    {
        Schema::table('movements', function (Blueprint $table) {
            $table->dropIndex(['active']);
            $table->foreignUuid('parish_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
            $table->index(['parish_id', 'active']);
        });
    }
};
