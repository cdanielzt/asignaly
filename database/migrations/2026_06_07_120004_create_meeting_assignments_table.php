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
        Schema::create('meeting_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('meeting_part_id')->constrained()->cascadeOnDelete();
            $table->enum('slot', ['main', 'helper']);
            $table->string('assignable_type')->nullable();
            $table->unsignedBigInteger('assignable_id')->nullable();
            $table->timestamps();

            $table->unique(['meeting_part_id', 'slot']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('meeting_assignments');
    }
};
