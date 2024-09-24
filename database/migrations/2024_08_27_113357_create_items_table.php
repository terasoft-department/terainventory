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
        Schema::create('items', function (Blueprint $table) {
            $table->increments('item_id');
        $table->string('item_name');
        $table->integer('itemcategory_id');
        $table->integer('quantity');
        $table->decimal('price', 8, 2);
        $table->integer('user_id');
         $table->integer('item_img')->nullable();
         $table->string('distribution')->nullable();
         $table->string('amount_distributed')->nullable();
          $table->string('total_amount')->nullable();
           $table->string('payment_method')->nullable();
            $table->string('customername')->nullable();
             $table->string('phone_number')->nullable();
            $table->string('status')->default('pending');
             $table->string('sold_at')->nullable();

        $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};
