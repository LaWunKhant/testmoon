<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id(); // This creates the BIGINT UNSIGNED auto-incrementing primary key
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade'); // Foreign key to tenants table
            $table->foreignId('house_id')->constrained()->onDelete('cascade'); // Foreign key to houses table
            $table->decimal('amount', 10, 2);
            $table->date('payment_date'); // Using DATE as initially discussed, change to ->dateTime() if needed
            $table->string('payment_method')->nullable(); // Made nullable based on potential unknown method initially
            $table->string('reference')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps(); // This creates created_at and updated_at columns
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
}
