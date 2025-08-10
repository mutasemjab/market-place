<?php

namespace App\Http\Controllers\Api\v1\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ShopCategory;

class ShopCategoryController extends Controller
{

    public function index(Request $request)
    {
        $cityId = $request->input('city_id');

        $categories = ShopCategory::with(['shops' => function ($query) use ($cityId) {
            if ($cityId) {
                $query->where('city_id', $cityId);
            }
        }])->get();

        return response()->json([
            'data' => $categories
        ]);
    }

}