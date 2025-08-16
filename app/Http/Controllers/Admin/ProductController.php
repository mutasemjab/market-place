<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\City;
use App\Models\Product;
use App\Models\ProductPhoto;
use App\Models\ProductVariation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with(['category', 'photos', 'variations'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        
        return view('admin.products.index', compact('products'));
    }

    public function create()
    {
        $categories = Category::get();
        return view('admin.products.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name_en' => 'required|string|max:255',
            'name_ar' => 'required|string|max:255',
            'description_en' => 'required|string',
            'description_ar' => 'required|string',
            'selling_price' => 'required|numeric|min:0',
            'tax' => 'numeric|min:0|max:100',
            'points' => 'numeric',
            'min_order' => 'required|integer|min:1',
            'status' => 'required|in:0,1',
            'is_featured' => 'required|in:0,1',
            'best_selling' => 'required|in:0,1',
            'photos.*' => 'image|mimes:jpeg,png,jpg,gif',
            'variation_names.*' => 'nullable|string|max:255',
            'variation_prices.*' => 'nullable|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            $product = Product::create([
                'category_id' => $request->category_id,
                'name_en' => $request->name_en,
                'name_ar' => $request->name_ar,
                'points' => $request->points,
                'description_en' => $request->description_en,
                'description_ar' => $request->description_ar,
                'selling_price' => $request->selling_price,
                'tax' => $request->tax ?? 16,
                'min_order' => $request->min_order,
                'status' => $request->status,
                'is_featured' => $request->is_featured,
                'best_selling' => $request->best_selling,
            ]);

            // Handle photos
            if ($request->hasFile('photos')) {
                foreach ($request->file('photos') as $photo) {
                    $photoPath = uploadImage('assets/admin/uploads', $photo);
                    ProductPhoto::create([
                        'product_id' => $product->id,
                        'photo' => $photoPath,
                    ]);
                }
            }

            // Handle variations
            if ($request->has('variation_names')) {
                foreach ($request->variation_names as $index => $name) {
                    if (!empty($name) && !empty($request->variation_prices[$index])) {
                        ProductVariation::create([
                            'product_id' => $product->id,
                            'name' => $name,
                            'price' => $request->variation_prices[$index],
                        ]);
                    }
                }
            }

            DB::commit();
            return redirect()->route('products.index')->with('success', 'Product created successfully!');

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Error creating product: ' . $e->getMessage());
        }
    }

    public function show(Product $product)
    {
        $product->load(['category', 'photos', 'variations']);
        return view('products.show', compact('product'));
    }

    public function edit(Product $product)
    {
        $categories = Category::where('status', 1)->get();
        $product->load(['photos', 'variations']);
        return view('admin.products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name_en' => 'required|string|max:255',
            'name_ar' => 'required|string|max:255',
            'description_en' => 'required|string',
            'description_ar' => 'required|string',
            'selling_price' => 'required|numeric|min:0',
            'tax' => 'numeric|min:0|max:100',
            'points' => 'numeric',
            'min_order' => 'required|integer|min:1',
            'status' => 'required|in:0,1',
            'is_featured' => 'required|in:0,1',
            'best_selling' => 'required|in:0,1',
            'photos.*' => 'image|mimes:jpeg,png,jpg,gif',
            'variation_names.*' => 'nullable|string|max:255',
            'variation_prices.*' => 'nullable|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            $product->update([
                'category_id' => $request->category_id,
                'name_en' => $request->name_en,
                'points' => $request->points,
                'name_ar' => $request->name_ar,
                'description_en' => $request->description_en,
                'description_ar' => $request->description_ar,
                'selling_price' => $request->selling_price,
                'tax' => $request->tax ?? 16,
                'min_order' => $request->min_order,
                'status' => $request->status,
                'is_featured' => $request->is_featured,
                'best_selling' => $request->best_selling,
            ]);

            // Handle new photos
            if ($request->hasFile('photos')) {
                foreach ($request->file('photos') as $photo) {
                    $photoPath = uploadImage('assets/admin/uploads', $photo);
                    ProductPhoto::create([
                        'product_id' => $product->id,
                        'photo' => $photoPath,
                    ]);
                }
            }

            // Update variations - delete existing and create new ones
            $product->variations()->delete();
            if ($request->has('variation_names')) {
                foreach ($request->variation_names as $index => $name) {
                    if (!empty($name) && !empty($request->variation_prices[$index])) {
                        ProductVariation::create([
                            'product_id' => $product->id,
                            'name' => $name,
                            'price' => $request->variation_prices[$index],
                        ]);
                    }
                }
            }

            DB::commit();
            return redirect()->route('products.index')->with('success', 'Product updated successfully!');

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Error updating product: ' . $e->getMessage());
        }
    }



    public function deletePhoto(ProductPhoto $photo)
    {
        try {
       
            $photo->delete();
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

        public function search(Request $request)
    {
        $term = $request->get('term');
        
        $products = Product::where('name_en', 'LIKE', '%' . $term . '%')
                        ->orWhere('name_ar', 'LIKE', '%' . $term . '%')
                        ->where('status', 1) // Only active products
                        ->select('id', 'name_en', 'name_ar', 'selling_price')
                        ->limit(20)
                        ->get();
        
        // Format the response with localized names
        $formattedProducts = $products->map(function($product) {
            return [
                'id' => $product->id,
                'name' => app()->getLocale() == 'ar' ? $product->name_ar : $product->name_en,
            ];
        });
        
        return response()->json($formattedProducts);
    }

    /**
     * Get product prices - fix the method name to match your route
     */
    public function getPrices($id)
    {
        $product = Product::find($id);
        
        if (!$product) {
            return response()->json([
                'selling_price' => null,
                'selling_price_for_user' => null,
            ], 404);
        }
        
        return response()->json([
            'selling_price' => $product->selling_price,
            'selling_price_for_user' => $product->selling_price_for_user ?? $product->selling_price, // fallback if no user price
        ]);
    }
}