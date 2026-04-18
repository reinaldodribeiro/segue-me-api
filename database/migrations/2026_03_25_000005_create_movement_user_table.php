<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('movement_user', function (Blueprint $table) {
            $table->foreignUuid('movement_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->primary(['movement_id', 'user_id']);
            $table->timestamps();
        });

        // Assign all existing movements to all existing users (so nobody loses access)
        $movements = DB::table('movements')->pluck('id');
        $users = DB::table('users')->pluck('id');
        $now = now()->toDateTimeString();

        $inserts = [];
        foreach ($users as $userId) {
            foreach ($movements as $movementId) {
                $inserts[] = [
                    'user_id' => $userId,
                    'movement_id' => $movementId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        if (! empty($inserts)) {
            foreach (array_chunk($inserts, 500) as $chunk) {
                DB::table('movement_user')->insert($chunk);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('movement_user');
    }
};
