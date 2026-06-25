<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('teams', function (Blueprint $table) {
            $table->id();
            
            // Basic Information
            $table->string('name');
            $table->string('code')->unique()->comment('Kode unik tim');
            $table->text('description')->nullable();
            
            // Leadership & Affiliation
            $table->foreignId('captain_id')->nullable()->constrained('players')->onDelete('set null');
            $table->foreignId('club_id')->nullable()->constrained('ptm_clubs')->onDelete('set null');
            
            // Status
            $table->boolean('is_active')->default(true);
            
            // Timestamps
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('name');
            $table->index('code');
            $table->index('captain_id');
            $table->index('club_id');
            $table->index('is_active');
            $table->index(['club_id', 'is_active']);
            $table->index(['is_active', 'created_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('teams');
    }
};