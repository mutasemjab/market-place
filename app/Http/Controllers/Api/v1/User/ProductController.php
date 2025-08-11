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
        try {
            // Try to get the authenticated user
            $token = $request->bearerToken();
            $authenticatedUser = null;

            if ($token) {
                $authenticatedUser = Auth::guard('user-api')->user();
            }

            // Auto-detect locale for Arabic text
            if ($request->filled('search') && preg_match('/[\x{0600}-\x{06FF}]/u', $request->search)) {
                app()->setLocale('ar');
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
                
                // Debug logging
                \Log::info('Search Parameters:', [
                    'search_term' => $searchTerm,
                    'locale' => $locale,
                    'is_arabic' => preg_match('/[\x{0600}-\x{06FF}]/u', $searchTerm)
                ]);
                
                $query->where(function ($q) use ($searchTerm, $locale) {
                    $q->where("name_{$locale}", 'LIKE', "%{$searchTerm}%")
                    ->orWhere("description_{$locale}", 'LIKE', "%{$searchTerm}%");
                    
                    // Also search in the other language as fallback
                    $otherLocale = $locale === 'ar' ? 'en' : 'ar';
                    $q->orWhere("name_{$otherLocale}", 'LIKE', "%{$searchTerm}%")
                    ->orWhere("description_{$otherLocale}", 'LIKE', "%{$searchTerm}%");
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
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $locale = app()->getLocale();

            switch ($sortBy) {
                case 'name':
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

            // Debug the final query
            \Log::info('Final Query:', [
                'sql' => $query->toSql(),
                'bindings' => $query->getBindings()
            ]);

            // Pagination
            $perPage = $request->get('per_page', 15);
            $products = $query->paginate($perPage);

            \Log::info('Search Results:', [
                'total_found' => $products->total(),
                'current_page' => $products->currentPage()
            ]);

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

            // Always return 200 with results (even if empty)
            return response()->json([
                'status' => true,
                'message' => $products->total() > 0 ? 'Products found successfully' : 'No products found',
                'data' => $products->items(),
                'pagination' => [
                    'current_page' => $products->currentPage(),
                    'last_page' => $products->lastPage(),
                    'per_page' => $products->perPage(),
                    'total' => $products->total(),
                    'from' => $products->firstItem(),
                    'to' => $products->lastItem(),
                ]
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Search Products Error: ' . $e->getMessage());
            
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while searching products',
                'data' => [],
                'pagination' => [
                    'current_page' => 1,
                    'last_page' => 1,
                    'per_page' => 15,
                    'total' => 0,
                    'from' => null,
                    'to' => null,
                ]
            ], 200); // Still return 200 as requested
        }
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
