<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add congregation_id as nullable first so existing rows are accepted
        foreach (['attendants', 'students', 'schedules', 'meetings'] as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->foreignId('congregation_id')
                    ->nullable()
                    ->after('id')
                    ->constrained()
                    ->cascadeOnDelete();
            });
        }

        // Create the default congregation for pre-existing data
        $congregationId = DB::table('congregations')->insertGetId([
            'name'       => 'Congregación Principal',
            'city'       => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Assign all existing data to the default congregation
        foreach (['attendants', 'students', 'schedules', 'meetings'] as $table) {
            DB::table($table)->update(['congregation_id' => $congregationId]);
        }

        // Assign all existing users to the default congregation as congregation_admin
        DB::table('users')->update([
            'congregation_id' => $congregationId,
            'role'            => 'congregation_admin',
        ]);

        // Now make congregation_id NOT NULL on tenant tables
        foreach (['attendants', 'students', 'schedules', 'meetings'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->foreignId('congregation_id')->nullable(false)->change();
            });
        }

        // Replace single-column unique indexes with congregation-scoped ones
        Schema::table('schedules', function (Blueprint $table) {
            $table->dropUnique('schedules_month_year_unique');
            $table->unique(['congregation_id', 'month', 'year']);
        });

        Schema::table('meetings', function (Blueprint $table) {
            $table->dropUnique('meetings_month_year_unique');
            $table->unique(['congregation_id', 'month', 'year']);
        });
    }

    public function down(): void
    {
        Schema::table('schedules', function (Blueprint $table) {
            $table->dropUnique(['congregation_id', 'month', 'year']);
            $table->unique(['month', 'year']);
        });

        Schema::table('meetings', function (Blueprint $table) {
            $table->dropUnique(['congregation_id', 'month', 'year']);
            $table->unique(['month', 'year']);
        });

        foreach (['attendants', 'students', 'schedules', 'meetings'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                $table->dropForeign([$tableName === $tableName ? 'congregation_id' : 'congregation_id']);
                $table->dropColumn('congregation_id');
            });
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
        });
    }
};
