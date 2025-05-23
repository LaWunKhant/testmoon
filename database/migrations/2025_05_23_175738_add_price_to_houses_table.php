<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPriceToHousesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('houses', function (Blueprint $table) {
            // *** Add the price column here ***
            // Using decimal(10, 2) is common for currency
            $table->decimal('price', 10, 2)->nullable()->after('description'); // Add a decimal column for price, allow nulls (or change if required), place after description
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('houses', function (Blueprint $table) {
            // *** Define how to remove the price column if rolling back ***
            $table->dropColumn('price');
        });
    }
}
