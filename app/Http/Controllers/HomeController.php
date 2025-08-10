<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Product;
use App\Models\Banner;
use App\Models\City;
use App\Models\Shop;
use App\Models\ShopCategory;
use Illuminate\Http\JsonResponse;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        $locale = app()->getLocale();
        $selectedCityId = $request->get('city_id', null);
        
        // Get all cities for the dropdown
        $cities = City::orderBy($locale == 'ar' ? 'name_ar' : 'name_en')->get();
        
        // Get banners for slider
        $banners = Banner::latest()->get();
        
        // Get categories based on selected city or all categories
        if ($selectedCityId) {
            $categories = ShopCategory::whereHas('cities', function($query) use ($selectedCityId) {
                $query->where('city_id', $selectedCityId);
            })->with(['cities'])->get();
        } else {
            $categories = ShopCategory::with(['cities'])->get();
        }
        
        // Get first category data for initial load
        $firstCategoryProducts = collect();
        if ($categories->count() > 0) {
            $firstCategory = $categories->first();
            $productsQuery = Shop::where('category_id', $firstCategory->id);
            
            if ($selectedCityId) {
                $productsQuery->where('city_id', $selectedCityId);
            }
            
            $firstCategoryProducts = $productsQuery->get();
        }
        
        return view('layouts.user', compact(
            'categories', 
            'firstCategoryProducts', 
            'banners', 
            'cities', 
            'selectedCityId',
            'locale'
        ));
    }

    /**
     * Filter categories by city (renamed from filterByCity)
     */
    public function filterByCity(Request $request)
    {
        $locale = app()->getLocale();
        $selectedCityId = $request->get('city_id', null);
        
        try {
            // Get categories based on selected city
            if ($selectedCityId) {
                $categories = ShopCategory::whereHas('cities', function($query) use ($selectedCityId) {
                    $query->where('city_id', $selectedCityId);
                })->with(['cities'])->get();
            } else {
                $categories = ShopCategory::with(['cities'])->get();
            }
            
            // Format categories for JavaScript
            $formattedCategories = $categories->map(function($category) use ($locale, $selectedCityId) {
                // Get products for this category and city
                $productsQuery = Shop::where('category_id', $category->id);
                if ($selectedCityId) {
                    $productsQuery->where('city_id', $selectedCityId);
                }
                $products = $productsQuery->get();
                
                return [
                    'id' => $category->id,
                    'name' => $locale == 'ar' ? $category->name_ar : $category->name_en,
                    'photo' => asset('assets/admin/uploads/' . $category->photo),
                    'products' => $products->map(function($product) use ($locale) {
                        return $this->formatProduct($product, $locale);
                    })->values()->toArray()
                ];
            })->values()->toArray();
            
            // Get first category products for initial display
            $firstCategoryProducts = collect();
            $firstCategory = null;
            
            if ($categories->count() > 0) {
                $firstCategory = $categories->first();
                $productsQuery = Shop::where('category_id', $firstCategory->id);
                
                if ($selectedCityId) {
                    $productsQuery->where('city_id', $selectedCityId);
                }
                
                $firstCategoryProducts = $productsQuery->get()->map(function($product) use ($locale) {
                    return $this->formatProduct($product, $locale);
                })->values()->toArray();
                
                $firstCategory = [
                    'id' => $firstCategory->id,
                    'name' => $locale == 'ar' ? $firstCategory->name_ar : $firstCategory->name_en,
                    'photo' => asset('assets/admin/uploads/' . $firstCategory->photo)
                ];
            }
            
            return response()->json([
                'success' => true,
                'categories' => $formattedCategories,
                'firstCategoryProducts' => $firstCategoryProducts,
                'firstCategory' => $firstCategory
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error in filterByCity', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error filtering categories: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get products for a specific category (Enhanced)
     */
    public function getCategoryProducts(Request $request)
    {
        $locale = app()->getLocale();
        $categoryId = $request->get('category_id');
        $selectedCityId = $request->get('city_id', null);
        
        // Add logging to debug
        \Log::info('getCategoryProducts called', [
            'category_id' => $categoryId,
            'city_id' => $selectedCityId,
            'locale' => $locale,
            'request_all' => $request->all()
        ]);
        
        try {
            if (!$categoryId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Category ID is required'
                ], 400);
            }
            
            // Get category details
            $category = ShopCategory::find($categoryId);
            if (!$category) {
                return response()->json([
                    'success' => false,
                    'message' => 'Category not found'
                ], 404);
            }
            
            // Build products query with both category and city filters
            $productsQuery = Shop::where('category_id', $categoryId);
            
            // Apply city filter if provided
            if ($selectedCityId) {
                $productsQuery->where('city_id', $selectedCityId);
            }
            
            // Add some debugging
            $sql = $productsQuery->toSql();
            $bindings = $productsQuery->getBindings();
            \Log::info('Products SQL Query', [
                'sql' => $sql,
                'bindings' => $bindings
            ]);
            
            $products = $productsQuery->get();
            
            \Log::info('Products found', [
                'count' => $products->count(),
                'category_id' => $categoryId,
                'city_id' => $selectedCityId
            ]);
            
            // Format products for JavaScript
            $formattedProducts = $products->map(function($product) use ($locale) {
                return $this->formatProduct($product, $locale);
            })->values()->toArray();
            
            // Format category info
            $categoryInfo = [
                'id' => $category->id,
                'name' => $locale == 'ar' ? $category->name_ar : $category->name_en,
                'photo' => asset('assets/admin/uploads/' . $category->photo)
            ];
            
            $response = [
                'success' => true,
                'products' => $formattedProducts,
                'category' => $categoryInfo,
                'debug' => [
                    'products_count' => $products->count(),
                    'category_id' => $categoryId,
                    'city_id' => $selectedCityId
                ]
            ];
            
            \Log::info('getCategoryProducts response', [
                'products_count' => count($formattedProducts),
                'category_id' => $categoryId
            ]);
            
            return response()->json($response);
            
        } catch (\Exception $e) {
            \Log::error('Error in getCategoryProducts', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'category_id' => $categoryId,
                'city_id' => $selectedCityId
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error loading products: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Helper method to format product data consistently
     */
    private function formatProduct($product, $locale)
    {
        // Handle specifications
        $specifications = $locale == 'ar' ? $product->specification_ar : $product->specification_en;
        
        if (is_string($specifications)) {
            $decoded = json_decode($specifications, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $specifications = $decoded;
            } else {
                $specifications = array_filter(explode(',', $specifications));
            }
        } elseif (!is_array($specifications)) {
            $specifications = [];
        }
        
        // Clean specifications
        $specifications = array_filter(array_map('trim', $specifications));
        
        return [
            'id' => $product->id,
            'name' => $locale == 'ar' ? $product->name_ar : $product->name_en,
            'description' => $locale == 'ar' ? 
                ($product->description_ar ?? 'منتج عالي الجودة') : 
                ($product->description_en ?? 'High quality product'),
            'photo' => asset('assets/admin/uploads/' . $product->photo),
            'rating' => (float) ($product->number_of_rating ?? 4.5),
            'reviews' => (int) ($product->number_of_review ?? 0),
            'specifications' => $specifications,
            'delivery_time' => $product->time_of_delivery ?? 
                ($locale == 'ar' ? '30-45 دقيقة' : '30-45 min'),
            'url' => $product->url ?? ''
        ];
    }
}