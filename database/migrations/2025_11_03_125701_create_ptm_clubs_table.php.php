<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('ptm_clubs', function (Blueprint $table) {
            $table->id();
            
            // Basic Information
            $table->string('name');
            $table->string('code')->unique()->comment('Kode unik klub');
            
            // Location Information
            $table->string('city')->nullable();
            $table->string('pic')->nullable();
            $table->string('province')->nullable();
            $table->text('address')->nullable();
            
            // Contact Information
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('website')->nullable();
            
            // Additional Information
            $table->text('description')->nullable();
            
            // Status
            $table->boolean('is_active')->default(true);
            
            // Timestamps
            $table->timestamps();
            
            // Indexes
            $table->index('code');
            $table->index('name');
            $table->index('city');
            $table->index('province');
            $table->index('is_active');
            $table->index(['is_active', 'city']);
            $table->index(['is_active', 'province']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('ptm_clubs');
    }
};