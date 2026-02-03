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
        Schema::create('inventory_items', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type'); // e.g., Computer, Mouse, Printer, Laptop
            $table->integer('quantity')->default(1);
            $table->string('serial_number')->nullable();
            $table->string('status')->default('Good'); // Good, Needs Repair, For Replacement
            $table->date('last_audit_date')->nullable();
            $table->text('description')->nullable();
            $table->string('location')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_items');
    }
};
