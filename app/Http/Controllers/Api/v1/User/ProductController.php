<?php

namespace App\Http\Controllers\Api\v1\User;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{


    public function productDetails(Request $request, $id)
    {
        // Try to get the authenticated user
        $token = $request->bearerToken();
        $authenticatedUser = null;

        if ($token) {
            $authenticatedUser = Auth::guard('user-api')->user();
        }

        // Fetch the product with related data
        $product = Product::with([
            'photos',
            'variations',
            'offers' => function ($query) {
                $query->whereDate('expired_at', '>', now());
            },
            'category'
        ])->find($id);

        if (!$product || $product->status != 1) {
            return response()->json([
                'status' => false,
                'message' => 'Product not found or inactive',
            ], 404);
        }

        // Add is_favourite flag
        $product->is_favourite = false;
        if ($authenticatedUser) {
            $product->is_favourite = $authenticatedUser
                ->favourites()
                ->where('product_id', $product->id)
                ->exists();
        }

        return response()->json([
            'data' => $product,
        ]);
    }

}
