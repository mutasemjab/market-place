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
                'data' => [],
            ], 200);
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

    public function searchProducts(Request $request)
    {
        // Try to get the authenticated user
        $token = $request->bearerToken();
        $authenticatedUser = null;

        if ($token) {
            $authenticatedUser = Auth::guard('user-api')->user();
        }

        // Start building the query
        $query = Product::with([
            'photos',
            'variations',
            'offers' => function ($query) {
                $query->whereDate('expired_at', '>', now());
            },
            'category'
        ])->where('status', 1); // Only active products

        // Search functionality
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $locale = app()->getLocale();
            
            $query->where(function ($q) use ($searchTerm, $locale) {
                $q->where("name_{$locale}", 'LIKE', "%{$searchTerm}%")
                ->orWhere("description_{$locale}", 'LIKE', "%{$searchTerm}%");
            });
        }

        // Filter by category
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Filter by price range
        if ($request->filled('min_price')) {
            $query->where('selling_price', '>=', $request->min_price);
        }

        if ($request->filled('max_price')) {
            $query->where('selling_price', '<=', $request->max_price);
        }

        // Filter by featured products
        if ($request->filled('is_featured') && $request->is_featured == 1) {
            $query->where('is_featured', 1);
        }

        // Filter by best selling products
        if ($request->filled('best_selling') && $request->best_selling == 1) {
            $query->where('best_selling', 1);
        }

        // Filter by products with offers
        if ($request->filled('has_offers') && $request->has_offers == 1) {
            $query->whereHas('offers', function ($q) {
                $q->whereDate('expired_at', '>', now());
            });
        }

        // Sorting functionality
        $sortBy = $request->get('sort_by', 'created_at'); // Default sort by creation date
        $sortOrder = $request->get('sort_order', 'desc'); // Default descending order

        switch ($sortBy) {
            case 'name':
                $locale = app()->getLocale();
                $query->orderBy("name_{$locale}", $sortOrder);
                break;
            case 'price':
                $query->orderBy('selling_price', $sortOrder);
                break;
            case 'category':
                $query->join('categories', 'products.category_id', '=', 'categories.id')
                    ->orderBy("categories.name_{$locale}", $sortOrder)
                    ->select('products.*');
                break;
            case 'featured':
                $query->orderBy('is_featured', $sortOrder);
                break;
            case 'best_selling':
                $query->orderBy('best_selling', $sortOrder);
                break;
            case 'created_at':
            default:
                $query->orderBy('created_at', $sortOrder);
                break;
        }

        // Pagination
        $perPage = $request->get('per_page', 15);
        $products = $query->paginate($perPage);

        // Add is_favourite flag to each product
        $products->getCollection()->transform(function ($product) use ($authenticatedUser) {
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
            'status' => true,
            'data' => $products->items(),
            'pagination' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
                'from' => $products->firstItem(),
                'to' => $products->lastItem(),
            ]
        ]);
    }

    public function offerProducts(Request $request)
    {
        // Try to get the authenticated user
        $token = $request->bearerToken();
        $authenticatedUser = null;

        if ($token) {
            $authenticatedUser = Auth::guard('user-api')->user();
        }

        // Build query for products with active offers
        $query = Product::with([
            'photos',
            'variations',
            'offers' => function ($query) {
                $query->whereDate('expired_at', '>', now())
                    ->orderBy('discount_percentage', 'desc'); // Order offers by discount percentage
            },
            'category'
        ])
        ->whereHas('offers', function ($q) {
            $q->whereDate('expired_at', '>', now());
        })
        ->where('status', 1); // Only active products

        // Optional: Filter by category
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Optional: Filter by minimum discount percentage
        if ($request->filled('min_discount')) {
            $query->whereHas('offers', function ($q) use ($request) {
                $q->whereDate('expired_at', '>', now())
                ->where('discount_percentage', '>=', $request->min_discount);
            });
        }

        // Optional: Search by product name
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $locale = app()->getLocale();
            
            $query->where(function ($q) use ($searchTerm, $locale) {
                $q->where("name_{$locale}", 'LIKE', "%{$searchTerm}%")
                ->orWhere("description_{$locale}", 'LIKE', "%{$searchTerm}%");
            });
        }

        

        // Pagination
        $perPage = $request->get('per_page', 15);
        $products = $query->paginate($perPage);

        // Add is_favourite flag and calculate offer details for each product
        $products->getCollection()->transform(function ($product) use ($authenticatedUser) {
            // Add is_favourite flag
            $product->is_favourite = false;
            if ($authenticatedUser) {
                $product->is_favourite = $authenticatedUser
                    ->favourites()
                    ->where('product_id', $product->id)
                    ->exists();
            }

            // Add offer summary (best offer details)
            if ($product->offers->isNotEmpty()) {
                $bestOffer = $product->offers->sortByDesc('discount_percentage')->first();
                $product->best_offer = [
                    'id' => $bestOffer->id,
                    'discount_percentage' => $bestOffer->discount_percentage,
                    'discount_amount' => $bestOffer->discount_amount ?? null,
                    'final_price' => $product->selling_price - ($product->selling_price * $bestOffer->discount_percentage / 100),
                    'expires_at' => $bestOffer->expired_at,
                    'expires_in_days' => now()->diffInDays($bestOffer->expired_at),
                ];
            }

            return $product;
        });

        return response()->json([
            'status' => true,
            'message' => 'Products with active offers retrieved successfully',
            'data' => $products->items(),
            'pagination' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
                'from' => $products->firstItem(),
                'to' => $products->lastItem(),
            ]
        ]);
    }





}
