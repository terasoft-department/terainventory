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
        Schema::create('sales', function (Blueprint $table) {
            $table->increments('sale_id');
             $table->integer('item_id')->nullable();
        $table->integer('amount_distributed')->nullable();
        $table->string('customername')->nullable();
        $table->string('phone_number')->nullable();
        $table->string('payment_method')->nullable();
        $table->string('sold_at')->nullable();
        $table->decimal('total_amount', 10, 2)->nullable();
        $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
