<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('tournaments', function (Blueprint $table) {
            $table->id();
            
            // Basic Information
            $table->string('name');
            $table->text('description')->nullable();
            
            // Tournament Type & Format
            $table->enum('type', ['single', 'double', 'duo', 'team'])->default('single');
            $table->enum('format', ['elimination', 'league', 'group'])->default('elimination');
            $table->enum('status', ['pending', 'registration_open', 'ongoing', 'completed', 'cancelled'])->default('pending');
            
            // Dates
            $table->date('start_date');
            $table->date('end_date');
            $table->date('registration_deadline');
            $table->timestamp('actual_start_date')->nullable();
            $table->timestamp('actual_end_date')->nullable();
            
            // Capacity
            $table->integer('max_players')->default(0);
            $table->integer('max_teams')->default(0);
            
            // Table & Time Management
            $table->integer('available_tables')->default(1);
            $table->integer('matches_per_table')->default(1);
            $table->integer('estimated_match_duration')->default(15)->comment('in minutes');
            $table->integer('break_between_matches')->default(5)->comment('in minutes');
            $table->integer('warmup_time')->default(5)->comment('in minutes');
            $table->time('daily_start_time')->nullable();
            $table->time('daily_end_time')->nullable();
            $table->integer('max_daily_playing_hours')->default(8);
            
            // Duration Tracking
            $table->integer('actual_duration_minutes')->default(0);
            $table->integer('estimated_duration_minutes')->default(0);
            
            // Settings
            $table->json('settings')->nullable();
            
            // Timestamps
            $table->timestamps();
            
            // Indexes
            $table->index('type');
            $table->index('format');
            $table->index('status');
            $table->index('start_date');
            $table->index('end_date');
            $table->index(['status', 'start_date']);
            $table->index(['type', 'status']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('tournaments');
    }
};