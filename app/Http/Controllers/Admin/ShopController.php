<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\City;
use App\Models\Shop;
use App\Models\ShopCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ShopController extends Controller
{
      public function index()
    {
        $shops = Shop::with('category')->paginate(10);
        return view('admin.shops.index', compact('shops'));
    }

    public function create()
    {
        // Load categories with their associated cities
        $categories = ShopCategory::with('cities')->get();
        
        return view('admin.shops.create', compact('categories'));
    }

   
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name_en' => 'required|string|max:255',
            'name_ar' => 'required|string|max:255',
            'url' => 'required|string|max:255',
            'description_en' => 'required|string',
            'description_ar' => 'required|string',
            'specification_en' => 'nullable|array',
            'specification_ar' => 'nullable|array',
            'number_of_review' => 'required|string',
            'number_of_rating' => 'required|string',
            'time_of_delivery' => 'required|string',
            'photo' => 'required|image|mimes:jpeg,png,jpg,gif',
            'category_id' => 'required|exists:categories,id',
            'city_id' => 'required|exists:cities,id',
        ]);

        // Handle file upload
        if ($request->hasFile('photo')) {
            $the_file_path = uploadImage('assets/admin/uploads', $request->photo);
            $validatedData['photo'] = $the_file_path;
        }

        // Convert specification arrays to JSON
        if ($request->has('specification_en')) {
            $validatedData['specification_en'] = json_encode($request->specification_en);
        }
        if ($request->has('specification_ar')) {
            $validatedData['specification_ar'] = json_encode($request->specification_ar);
        }

        Shop::create($validatedData);

        return redirect()->route('shops.index')->with('success', 'shop created successfully!');
    }


    public function edit(Shop $shop)
    {
        $categories = ShopCategory::all();
        $cities = City::all();
        return view('admin.shops.edit', compact('shop', 'categories','cities'));
    }

    public function update(Request $request, Shop $shop)
    {
        $validatedData = $request->validate([
            'name_en' => 'required|string|max:255',
            'name_ar' => 'required|string|max:255',
            'url' => 'required|string|max:255',
            'description_en' => 'required|string',
            'description_ar' => 'required|string',
            'specification_en' => 'nullable|array',
            'specification_ar' => 'nullable|array',
            'number_of_review' => 'required|string',
            'number_of_rating' => 'required|string',
            'time_of_delivery' => 'required|string',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif',
            'category_id' => 'required|exists:categories,id',
            'city_id' => 'required|exists:cities,id',
        ]);

        // Handle file upload
        if ($request->hasFile('photo')) {
            $the_file_path = uploadImage('assets/admin/uploads', $request->photo);
            $validatedData['photo'] = $the_file_path;
        }

        // Convert specification arrays to JSON
        if ($request->has('specification_en')) {
            $validatedData['specification_en'] = json_encode($request->specification_en);
        }
        if ($request->has('specification_ar')) {
            $validatedData['specification_ar'] = json_encode($request->specification_ar);
        }

        $shop->update($validatedData);

        return redirect()->route('shops.index')->with('success', 'shop updated successfully!');
    }

    public function destroy(Shop $shop)
    {
     
        $shop->delete();

        return redirect()->route('shops.index')->with('success', 'Product deleted successfully!');
    }
}