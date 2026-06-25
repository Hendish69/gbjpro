<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('sets', function (Blueprint $table) {
            $table->id();
            
            // Foreign Keys
            $table->foreignId('match_id')->constrained()->onDelete('cascade');
            
            // Set Information
            $table->integer('set_number')->default(1);
            $table->integer('player1_score')->default(0);
            $table->integer('player2_score')->default(0);
            $table->integer('team1_score')->default(0);
            $table->integer('team2_score')->default(0);
            
            // Set Details
            $table->integer('duration_minutes')->nullable()->comment('Duration of this set in minutes');
            $table->json('point_sequence')->nullable()->comment('Sequence of points for detailed analysis');
            $table->text('notes')->nullable();
            
            // Timestamps
            $table->timestamps();
            
            // Indexes
            $table->index('match_id');
            $table->index(['match_id', 'set_number']);
            $table->index('set_number');
        });
    }

    public function down()
    {
        Schema::dropIfExists('sets');
    }
};