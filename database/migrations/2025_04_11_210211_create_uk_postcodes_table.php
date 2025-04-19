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
        Schema::create('uk_postcodes', function (Blueprint $table) {
            $table->text('postcode')->nullable();
            $table->text('in_use')->nullable();
            $table->text('latitude')->nullable();
            $table->text('longitude')->nullable();
            $table->text('easting')->nullable();
            $table->text('northing')->nullable();
            $table->text('grid_ref')->nullable();
            $table->text('county')->nullable();
            $table->text('district')->nullable();
            $table->text('ward')->nullable();
            $table->text('district_code')->nullable();
            $table->text('ward_code')->nullable();
            $table->text('country')->nullable();
            $table->text('county_code')->nullable();
            $table->text('introduced')->nullable();
            $table->text('terminated')->nullable();
            $table->text('parish')->nullable();
            $table->text('national_park')->nullable();
            $table->text('population')->nullable();
            $table->text('households')->nullable();
            $table->text('built_up_area')->nullable();
            $table->text('lower_layer_super_output_area')->nullable();
            $table->text('rural_urban')->nullable();
            $table->text('region')->nullable();
            $table->text('altitude')->nullable();
            $table->text('london_zone')->nullable();
            $table->text('lsoa_code')->nullable();
            $table->text('local_authority')->nullable();
            $table->text('msoa_code')->nullable();
            $table->text('middle_layer_super_output_area')->nullable();
            $table->text('parish_code')->nullable();
            $table->text('census_output_area')->nullable();
            $table->text('index_of_multiple_deprivation')->nullable();
            $table->text('quality')->nullable();
            $table->text('user_type')->nullable();
            $table->text('last_updated')->nullable();
            $table->text('nearest_station')->nullable();
            $table->text('distance_to_station')->nullable();
            $table->text('postcode_area')->nullable();
            $table->text('postcode_district')->nullable();
            $table->text('police_force')->nullable();
            $table->text('water_company')->nullable();
            $table->text('plus_code')->nullable();
            $table->text('average_income')->nullable();
            $table->text('sewage_company')->nullable();
            $table->text('travel_to_work_area')->nullable();
            $table->text('itl_level_2')->nullable();
            $table->text('itl_level_3')->nullable();
            $table->text('uprns')->nullable();
            $table->text('distance_to_sea')->nullable();
            $table->text('lsoa21_code')->nullable();
            $table->text('lower_layer_super_output_area_2021')->nullable();
            $table->text('msoa21_code')->nullable();
            $table->text('middle_layer_super_output_area_2021')->nullable();
            $table->text('census_output_area_2021')->nullable();
            $table->text('imd_decile')->nullable();
            $table->text('constituency_code_2024')->nullable();
            $table->text('constituency_name_2024')->nullable();
            $table->text('property_type')->nullable();
            $table->text('roads')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('uk_postcodes');
    }
};
