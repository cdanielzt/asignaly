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
        Schema::create('meeting_parts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('meeting_week_id')->constrained()->cascadeOnDelete();
            $table->enum('section', ['header', 'tesoros', 'maestros', 'vida_cristiana', 'closing']);
            $table->unsignedTinyInteger('position');
            $table->string('title');
            $table->string('duration')->nullable();
            $table->enum('type', ['display_only', 'single_attendant', 'single_student', 'student_helper', 'dual_attendant']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('meeting_parts');
    }
};
