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
        Schema::create('barangay_stocks', function (Blueprint $table) {
            $table->id();
            $table->string('item_name');
            $table->text('description')->nullable();
            $table->integer('quantity')->default(0);
            $table->integer('low_stock_threshold')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('barangay_stocks');
    }
};
