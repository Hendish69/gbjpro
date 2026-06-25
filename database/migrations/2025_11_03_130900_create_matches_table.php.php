<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('matches', function (Blueprint $table) {
            $table->id();
            
            // Tournament & Match Information
            $table->foreignId('tournament_id')->constrained()->onDelete('cascade');
            $table->enum('match_type', ['single', 'double', 'team'])->default('single');
            $table->enum('match_format', ['best_of_3', 'best_of_5', 'best_of_7'])->default('best_of_3');
            
            // Player/Team Participants
            $table->foreignId('player1_id')->nullable()->constrained('players')->onDelete('set null');
            $table->foreignId('player2_id')->nullable()->constrained('players')->onDelete('set null');
            $table->foreignId('player1_partner_id')->nullable()->constrained('players')->onDelete('set null');
            $table->foreignId('player2_partner_id')->nullable()->constrained('players')->onDelete('set null');
            $table->foreignId('team1_id')->nullable()->constrained('teams')->onDelete('set null');
            $table->foreignId('team2_id')->nullable()->constrained('teams')->onDelete('set null');
            
            // Bracket & Round Information
            $table->enum('bracket_type', ['winner', 'loser', 'final', 'qualification'])->nullable();
            $table->integer('round_number')->default(1);
            $table->integer('match_number')->default(1);
            $table->string('group_name')->nullable();
            
            // Match Scheduling
            $table->timestamp('match_date')->nullable();
            $table->foreignId('table_id')->nullable()->constrained('tables')->onDelete('set null');
            $table->integer('duration_minutes')->nullable()->comment('Actual duration in minutes');
            
            // Match Status & Results
            $table->enum('status', ['scheduled', 'ongoing', 'completed', 'cancelled', 'walkover'])->default('scheduled');
            $table->foreignId('winner_id')->nullable()->constrained('players')->onDelete('set null');
            $table->enum('winning_side', ['player1', 'player2', 'team1', 'team2', 'draw'])->nullable();
            
            // Scores
            $table->integer('player1_score')->default(0);
            $table->integer('player2_score')->default(0);
            $table->integer('team1_score')->default(0);
            $table->integer('team2_score')->default(0);
            $table->json('set_scores')->nullable()->comment('Detailed set scores');
            
            // Bracket Progression
            $table->foreignId('next_match_id')->nullable()->constrained('matches')->onDelete('set null');
            $table->enum('next_match_position', ['player1', 'player2'])->nullable();
            $table->foreignId('parent_match1_id')->nullable()->constrained('matches')->onDelete('set null');
            $table->foreignId('parent_match2_id')->nullable()->constrained('matches')->onDelete('set null');
            
            // Additional Information
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable()->comment('Additional match data');
            
            // Timestamps
            $table->timestamps();
            
            // Indexes
            $table->index('tournament_id');
            $table->index('match_type');
            $table->index('status');
            $table->index('match_date');
            $table->index('table_id');
            $table->index('round_number');
            $table->index('bracket_type');
            $table->index('group_name');
            $table->index('player1_id');
            $table->index('player2_id');
            $table->index('team1_id');
            $table->index('team2_id');
            $table->index('winner_id');
            $table->index(['tournament_id', 'round_number']);
            $table->index(['tournament_id', 'status']);
            $table->index(['tournament_id', 'group_name']);
            $table->index(['match_date', 'status']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('matches');
    }
};