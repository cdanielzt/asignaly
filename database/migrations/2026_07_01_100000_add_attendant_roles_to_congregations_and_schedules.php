<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('congregations', function (Blueprint $table) {
            $table->json('attendant_roles')->nullable()->after('city');
        });

        Schema::table('schedules', function (Blueprint $table) {
            $table->json('roles')->nullable()->after('name');
        });
    }

    public function down(): void
    {
        Schema::table('congregations', function (Blueprint $table) {
            $table->dropColumn('attendant_roles');
        });

        Schema::table('schedules', function (Blueprint $table) {
            $table->dropColumn('roles');
        });
    }
};
