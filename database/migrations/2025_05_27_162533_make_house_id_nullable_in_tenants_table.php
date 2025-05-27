<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MakeHouseIdNullableInTenantsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            // *** Modify the existing house_id column to be nullable ***
            $table->foreignId('house_id')->nullable()->change(); // Find the existing foreignId column and make it nullable
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            // *** Define how to revert the house_id column back to non-nullable if rolling back ***
            // Note: This might fail if there are existing rows with NULL house_id when rolling back.
            // You might need to manually handle those rows or drop the column entirely if rollback is tricky.
            $table->foreignId('house_id')->change(); // Attempt to revert back to its original definition (likely non-nullable)
            // Or, if you're sure of the original definition:
            // $table->foreignId('house_id')->constrained()->change(); // If it had a foreign key constraint
        });
    }
}
