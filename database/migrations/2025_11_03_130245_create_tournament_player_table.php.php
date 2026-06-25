<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('tournament_player', function (Blueprint $table) {
            $table->id();
            
            // Foreign Keys
            $table->foreignId('tournament_id')->constrained()->onDelete('cascade');
            $table->foreignId('player_id')->constrained()->onDelete('cascade');
            
            // Tournament Specific Data
            $table->integer('seed')->nullable()->comment('Seed position in tournament');
            $table->string('group')->nullable()->comment('Group assignment');
            
            // Representation Information
            $table->foreignId('representing_ptm_club_id')->nullable()->constrained('ptm_clubs')->onDelete('set null');
            $table->boolean('is_representing_different_club')->default(false);
            $table->text('representation_notes')->nullable();
            
            // Timestamps
            $table->timestamps();
            
            // Indexes
            $table->unique(['tournament_id', 'player_id']);
            $table->index('tournament_id');
            $table->index('player_id');
            $table->index('seed');
            $table->index('group');
            $table->index('representing_ptm_club_id');
            $table->index(['tournament_id', 'group']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('tournament_player');
    }
};