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
        Schema::create('shops', function (Blueprint $table) {
            $table->id();
            $table->string('name_en');
            $table->string('name_ar');
            $table->text('description_ar');
            $table->text('description_en');
            $table->json('specification_ar')->nullable();
            $table->json('specification_en')->nullable();
            $table->string('number_of_review');
            $table->string('number_of_rating');
            $table->string('time_of_delivery');
            $table->string('url');
            $table->string('photo');
            $table->unsignedBigInteger('category_id');
            $table->foreign('category_id')->references('id')->on('shop_categories')->onDelete('cascade');
            $table->unsignedBigInteger('city_id');
            $table->foreign('city_id')->references('id')->on('cities')->onDelete('cascade');

            $table->timestamps();
        });

        DB::table('shops')->insert([
            'name_en' => 'Tasty Bites',
            'name_ar' => 'لقمات شهية',
            'description_ar' => 'مطعم يقدم أشهى الوجبات السريعة.',
            'description_en' => 'A restaurant serving delicious fast food.',
            'specification_ar' => json_encode(['النوع' => 'وجبات سريعة', 'السعر' => 'مناسب']),
            'specification_en' => json_encode(['type' => 'Fast Food', 'price' => 'Affordable']),
            'number_of_review' => '320',
            'number_of_rating' => '4.5',
            'time_of_delivery' => '30 mins',
            'url' => 'https://tastybites.com',
            'photo' => 'shops/tasty_bites.jpg',
            'category_id' => 1, // make sure this ID exists in shop_categories
            'city_id' => 1,     // make sure this ID exists in cities
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('shops');
    }
};
