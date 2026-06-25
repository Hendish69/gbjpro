<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('team_player', function (Blueprint $table) {
            $table->id();
            
            // Foreign Keys
            $table->foreignId('team_id')->constrained()->onDelete('cascade');
            $table->foreignId('player_id')->constrained()->onDelete('cascade');
            
            // Role & Position
            $table->string('role')->default('member')->comment('captain, vice_captain, member, etc.');
            $table->integer('position')->nullable()->comment('Urutan posisi dalam tim');
            
            // Timestamps
            $table->timestamps();
            
            // Indexes
            $table->unique(['team_id', 'player_id']);
            $table->index('team_id');
            $table->index('player_id');
            $table->index('role');
            $table->index('position');
            $table->index(['team_id', 'role']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('team_player');
    }
};