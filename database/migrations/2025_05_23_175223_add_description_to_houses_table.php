<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDescriptionToHousesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('houses', function (Blueprint $table) {
            // *** Add the description column here ***
            $table->text('description')->nullable()->after('address'); // Add a text column for description, allow nulls, place after address
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('houses', function (Blueprint $table) {
            // *** Define how to remove the description column if rolling back ***
            $table->dropColumn('description');
        });
    }
}
