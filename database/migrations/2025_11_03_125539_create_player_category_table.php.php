<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('player_category', function (Blueprint $table) {
            $table->id();
            $table->foreignId('player_id')->constrained()->onDelete('cascade');
            $table->foreignId('player_category_id')->constrained()->onDelete('cascade');
            $table->timestamps();
            
            $table->unique(['player_id', 'player_category_id']);
            $table->index('player_id');
            $table->index('player_category_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('player_category');
    }
};