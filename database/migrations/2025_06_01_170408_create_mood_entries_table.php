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
        Schema::create('mood_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->integer('mood_level')->comment('1-5 scale: 1=very bad, 2=bad, 3=neutral, 4=good, 5=very good');
            $table->date('entry_date')->comment('Date the mood was recorded for');
            $table->text('notes')->nullable()->comment('Optional notes about the mood/day');
            $table->json('activities')->nullable()->comment('Array of activities/tags for the day');
            $table->time('entry_time')->nullable()->comment('Time when mood was recorded');
            $table->timestamps();

            // Add indexes for performance
            $table->index(['user_id', 'entry_date']);
            $table->index(['user_id', 'mood_level']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mood_entries');
    }
};
