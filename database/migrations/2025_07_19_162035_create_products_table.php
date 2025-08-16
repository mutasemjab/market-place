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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('category_id');
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');
            $table->string('name_en');
            $table->string('name_ar');
            $table->text('description_ar');
            $table->text('description_en');
            $table->double('selling_price');
            $table->double('tax')->default(16);
            $table->integer('min_order');
            $table->double('points')->default(0);
            $table->tinyInteger('status'); //0 not active //1 active
            $table->tinyInteger('is_featured')->default(1); //0 not //1 yes
            $table->tinyInteger('is_favourite')->default(0); //0 not //1 yes
            $table->tinyInteger('best_selling')->default(0); //0 not //1 yes
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
        Schema::dropIfExists('products');
    }
};
