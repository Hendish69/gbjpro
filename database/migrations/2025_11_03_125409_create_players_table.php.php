<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('players', function (Blueprint $table) {
            $table->id();
            
            // Basic Information
            $table->string('name');
            $table->string('nickname')->nullable();
            $table->string('ptm_number')->nullable()->unique();
            $table->foreignId('ptm_club_id')->nullable()->constrained('ptm_clubs')->onDelete('set null');
            $table->foreignId('representing_ptm_club_id')->nullable()->constrained('ptm_clubs')->onDelete('set null');
            
            // Ranking Information
            $table->integer('division_ranking')->default(11)->comment('1-11, 1 being the highest');
            $table->integer('previous_division_ranking')->nullable();
            
            // Contact Information
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->enum('gender', ['male', 'female'])->nullable();
            
            // Profile Information
            $table->string('photo')->nullable();
            $table->text('bio')->nullable();
            
            // Playing Information
            $table->string('playing_style')->nullable();
            $table->string('grip_style')->nullable();
            $table->json('preferences')->nullable()->comment('Player preferences and settings');
            
            // Status Flags
            $table->boolean('is_active')->default(true);
            $table->boolean('is_in_library')->default(false);
            
            // Statistics
            $table->integer('total_tournaments')->default(0);
            $table->timestamp('last_played_at')->nullable();
            
            // Timestamps
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('division_ranking');
            $table->index('ptm_club_id');
            $table->index('is_active');
            $table->index('is_in_library');
            $table->index('last_played_at');
            $table->index(['division_ranking', 'is_active']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('players');
    }
};