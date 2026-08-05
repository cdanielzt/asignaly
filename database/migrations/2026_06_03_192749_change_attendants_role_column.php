<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('attendants', function (Blueprint $table) {
            $table->dropColumn('is_elder');
            $table->string('role')->default('Publisher')->after('name');
        });
    }

    public function down(): void
    {
        Schema::table('attendants', function (Blueprint $table) {
            $table->dropColumn('role');
            $table->boolean('is_elder')->default(false)->after('name');
        });
    }
};
