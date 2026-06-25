<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('player_representation_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('player_id')->constrained()->onDelete('cascade');
            $table->foreignId('tournament_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('original_ptm_club_id')->constrained('ptm_clubs')->onDelete('cascade');
            $table->foreignId('representing_ptm_club_id')->constrained('ptm_clubs')->onDelete('cascade');
            $table->string('reason');
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index('player_id');
            $table->index('tournament_id');
            $table->index(['player_id', 'created_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('player_representation_histories');
    }
};