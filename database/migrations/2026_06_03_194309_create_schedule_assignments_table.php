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
        Schema::create('schedule_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('schedule_week_id')->constrained()->cascadeOnDelete();
            $table->enum('meeting_day', ['friday', 'saturday']);
            $table->string('role');
            $table->foreignId('attendant_id')->nullable()->nullOnDelete()->constrained();
            $table->timestamps();

            $table->unique(['schedule_week_id', 'meeting_day', 'role']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedule_assignments');
    }
};
