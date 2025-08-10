<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cart_variations', function (Blueprint $table) {
            $table->id();
              $table->unsignedBigInteger('variation_id')->nullable();
            $table->foreign('variation_id')->references('id')->on('product_variations')->onDelete('cascade');
              $table->unsignedBigInteger('cart_id')->nullable();
            $table->foreign('cart_id')->references('id')->on('carts')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cart_variations');
    }
};
