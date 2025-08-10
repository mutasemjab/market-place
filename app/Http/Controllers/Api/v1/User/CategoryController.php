<?php

namespace App\Http\Controllers\Api\v1\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\ProductResource;
use App\Http\Resources\SubCategoryResource;
use App\Models\Category;
use App\Models\SubCategory;
use App\Models\Favorite;
use App\Models\Product;
use App\Models\UserAddress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class CategoryController extends Controller
{

    public function index(Request $request)
    {
        $categories = Category::get();
    
        return response()->json(['data' => $categories]);
    }

 


    public function getProducts(Request $request, $id)
    {
        // Try to get the authenticated user
        $token = $request->bearerToken();
        $authenticatedUser = null;

        if ($token) {
            $authenticatedUser = Auth::guard('user-api')->user();
        }

        // Find the category
        $category = Category::find($id);
        if (!$category) {
            return response()->json([
                'status' => false,
                'message' => 'Category not found',
            ], 404);
        }

        // Get products with relationships
        $products = Product::where('category_id', $id)
            ->where('status', 1)
            ->with(['photos', 'variations', 'offers' => function ($query) {
                $query->whereDate('expired_at', '>', now());
            }])
            ->get()
            ->map(function ($product) use ($authenticatedUser) {
                $product->is_favourite = false;

                if ($authenticatedUser) {
                    $product->is_favourite = $authenticatedUser
                        ->favourites()
                        ->where('product_id', $product->id)
                        ->exists();
                }

                return $product;
            });

        return response()->json([
            'category_name' => app()->getLocale() === 'ar' ? $category->name_ar : $category->name_en,
            'data' => $products,
        ]);
    }


}
