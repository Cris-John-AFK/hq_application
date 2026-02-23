<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('inventory_items', function (Blueprint $table) {
            $table->id();
            $table->string('department')->nullable();
            $table->string('location')->nullable();
            $table->string('name'); // Nomenclature
            $table->string('type'); // Derived Type
            $table->string('brand')->nullable();
            $table->string('model_name')->nullable();
            $table->string('color')->nullable();
            $table->string('serial_number')->nullable();
            $table->string('asset_tag')->nullable();
            $table->string('status')->default('Good');
            $table->integer('quantity')->default(1);
            $table->date('last_audit_date')->nullable();
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
