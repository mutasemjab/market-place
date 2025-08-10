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
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('shop_id');
            $table->foreign('shop_id')->references('id')->on('shops')->onDelete('cascade');
            $table->string('name_en');
            $table->string('name_ar');
            $table->timestamps();
        });
         DB::table('categories')->insert([
            ['name_en'=>'Burger','name_ar'=>'برغر','shop_id'=>1],
            ['name_en'=>'shawerma','name_ar'=>'شاورما','shop_id'=>1]
          ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('categories');
    }
};
