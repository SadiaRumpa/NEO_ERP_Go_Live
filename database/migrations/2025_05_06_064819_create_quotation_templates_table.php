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
        Schema::create('quotation_templates', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('job_type_id')->unique();
            $table->string('job_type_name', 200)->nullable();
            $table->string('section_name');
            $table->string('section_slug');
            $table->string('section_type', 100);
            $table->boolean('is_required')->default(0);
            $table->boolean('has_attachments')->default(0);
            $table->string('attachment_type')->nullable();
            $table->integer('order')->default(0);
            $table->boolean('status')->default(1);
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('job_type_id')->references('id')->on('job_types')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quotation_templates');
    }
};
