<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCapacityAndPhotoPathToHousesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('houses', function (Blueprint $table) {
            // *** Add the capacity column here ***
            $table->integer('capacity')->nullable()->after('price'); // Add an integer column for capacity, allow nulls, place after price

            // *** Add the photo_path column here ***
            $table->string('photo_path')->nullable()->after('capacity'); // Add a string column for the path, allow nulls, place after capacity
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('houses', function (Blueprint $table) {
            // *** Define how to remove the columns if rolling back ***
            $table->dropColumn('capacity');
            $table->dropColumn('photo_path');
        });
    }
}
