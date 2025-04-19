<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('enquiry_addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enquiry_id')->constrained('enquiries')->onDelete('cascade');
            $table->string('contact_firstname');
            $table->string('contact_lastname')->nullable();
            $table->string('contact_phone')->nullable();
            $table->string('contact_mobile')->nullable();
            $table->string('contact_email')->nullable();
            $table->string('address', 100)->nullable();
            $table->string('county', 100)->nullable();
            $table->string('postcode', 50)->nullable();
            $table->string('country', 100)->nullable();
            $table->text('location')->nullable();            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('enquiry_addresses');
    }
};
