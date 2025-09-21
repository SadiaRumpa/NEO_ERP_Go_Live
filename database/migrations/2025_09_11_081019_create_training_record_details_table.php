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
        Schema::create('training_record_details', function (Blueprint $table) {
            $table->id();
            $table->integer('training_record_id');
            $table->integer('training_id');
            $table->string('training_title');
            $table->date('earned_at');
            $table->date('valid_till');
            $table->string('certificate');
            $table->text('notes');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('training_record_details');
    }
};
