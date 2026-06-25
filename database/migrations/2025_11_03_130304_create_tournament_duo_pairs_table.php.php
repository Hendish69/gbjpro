<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('tournament_duo_pairs', function (Blueprint $table) {
            $table->id();
            
            // Foreign Keys
            $table->foreignId('tournament_id')->constrained()->onDelete('cascade');
            
            // Pair Information
            $table->string('pair_name')->nullable()->comment('Custom pair name');
            $table->string('team_name')->nullable()->comment('Team name if applicable');
            
            // Player 1 Information
            $table->foreignId('player1_id')->constrained('players')->onDelete('cascade');
            $table->foreignId('player1_club_id')->nullable()->constrained('ptm_clubs')->onDelete('set null');
            
            // Player 2 Information
            $table->foreignId('player2_id')->constrained('players')->onDelete('cascade');
            $table->foreignId('player2_club_id')->nullable()->constrained('ptm_clubs')->onDelete('set null');
            
            // Status & Notes
            $table->enum('status', ['pending', 'confirmed', 'cancelled'])->default('pending');
            $table->text('notes')->nullable();
            
            // Timestamps
            $table->timestamps();
            
            // Indexes
            $table->index('tournament_id');
            $table->index('player1_id');
            $table->index('player2_id');
            $table->index('status');
            $table->index(['tournament_id', 'status']);
            $table->index('pair_name');
            
            // Ensure unique pairs in tournament
            $table->unique(['tournament_id', 'player1_id', 'player2_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('tournament_duo_pairs');
    }
};