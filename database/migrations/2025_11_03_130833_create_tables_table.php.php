<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('tables', function (Blueprint $table) {
            $table->id();
            
            // Basic Information
            $table->string('name');
            $table->string('location')->nullable();
            $table->text('description')->nullable();
            
            // Status
            $table->enum('status', ['available', 'occupied', 'maintenance'])->default('available');
            
            // Usage Statistics
            $table->integer('total_usage_minutes')->default(0);
            $table->integer('total_matches_played')->default(0);
            
            // Maintenance Information
            $table->timestamp('last_maintenance_date')->nullable();
            $table->text('maintenance_notes')->nullable();
            
            // Timestamps
            $table->timestamps();
            
            // Indexes
            $table->index('name');
            $table->index('status');
            $table->index('location');
            $table->index(['status', 'location']);
            $table->index('last_maintenance_date');
        });
    }

    public function down()
    {
        Schema::dropIfExists('tables');
    }
};