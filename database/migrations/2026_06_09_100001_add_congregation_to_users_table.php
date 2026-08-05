<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('congregation_id')
                ->nullable()
                ->after('id')
                ->constrained()
                ->nullOnDelete();

            $table->enum('role', ['super_admin', 'congregation_admin', 'member'])
                ->default('member')
                ->after('congregation_id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['congregation_id']);
            $table->dropColumn(['congregation_id', 'role']);
        });
    }
};
