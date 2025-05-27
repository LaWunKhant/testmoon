<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPasswordToTenantsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            // *** Add the password column here ***
            // Password columns are typically strings and non-nullable
            $table->string('password')->nullable()->after('rent'); // Add a string column for the password, allow nulls for now, place after rent. *** Change to non-nullable later after implementing registration/seeding with passwords ***

            // You might also add a remember_token for "remember me" functionality
            // $table->rememberToken();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            // *** Define how to remove the password column if rolling back ***
            $table->dropColumn('password');
            // If you added remember_token, drop it here too
            // $table->dropColumn('remember_token');
        });
    }
}
