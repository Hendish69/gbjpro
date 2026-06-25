<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('division_ranking_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('player_id')->constrained()->onDelete('cascade');
            $table->integer('old_ranking');
            $table->integer('new_ranking');
            $table->string('reason');
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index('player_id');
            $table->index(['player_id', 'created_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('division_ranking_histories');
    }
};