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
        Schema::create('business_info', function (Blueprint $table) {
            $table->id();
            $table->string('business_name');
            $table->string('business_slug')->nullable();
            $table->string('business_short_name')->nullable();
            $table->string('business_domain')->nullable();
            $table->string('business_allowed_file_types')->nullable();
            $table->string('business_logo')->nullable();
            $table->string('business_logo_dark')->nullable();
            $table->string('business_favicon')->nullable();
            $table->string('business_address')->nullable();
            $table->string('business_address2')->nullable();
            $table->string('business_city')->nullable();
            $table->string('business_state')->nullable();
            $table->string('business_zip')->nullable();
            $table->string('business_country')->nullable();
            $table->string('business_phone')->nullable();
            $table->string('business_email')->nullable();
            $table->string('business_website')->nullable();
            $table->string('business_registration_number')->nullable();
            $table->string('business_registration_number2')->nullable();
            $table->date('business_start_date')->nullable();
            $table->string('business_status');

            $table->string('business_brand_app_name')->default('NEOERP');
            $table->text('business_brand_logo_on_top_left')->nullable();
            $table->tinyInteger('business_brand_credit_on_footer_enable')->default(1);
            $table->text('business_brand_credit_on_footer')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('business_info');
    }
};
