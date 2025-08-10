<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\City;
use App\Models\Coupon;
use App\Models\Service;
use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::get();
        return view('admin.categories.index', compact('categories'));
    }

    public function create()
    {
     $shops = Shop::get();     
     return view('admin.categories.create',compact('shops'));
    }


    public function store(Request $request)
    {
        // Validate the request
        $request->validate([
            'name_en' => 'required|string|max:255',
            'name_ar' => 'required|string|max:255',
            'cities' => 'required|array|min:1',
            'cities.*' => 'exists:cities,id',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        try {
            $category = new Category();
            
            $category->name_en = $request->get('name_en');
            $category->name_ar = $request->get('name_ar');
            $category->shop_id = $request->get('shop');
            
            if ($request->has('photo')) {
                $the_file_path = uploadImage('assets/admin/uploads', $request->photo);
                $category->photo = $the_file_path;
            }
            
            if ($category->save()) {
                
                return redirect()->route('categories.index')->with(['success' => 'Category created successfully']);
            } else {
                return redirect()->back()->with(['error' => 'Something went wrong']);
            }
        } catch (\Exception $ex) {
            return redirect()->back()
                ->with(['error' => 'Sorry, an error occurred: ' . $ex->getMessage()])
                ->withInput();
        }
    }

    public function edit($id)
    {
        $category = Category::with('cities')->findOrFail($id);
        $shops = Shop::all(); // Get all cities for the dropdown
        return view('admin.categories.edit', compact('category', 'shops'));
    }

    public function update(Request $request, $id)
    {
        // Validate the request
        $request->validate([
            'name_en' => 'required|string|max:255',
            'name_ar' => 'required|string|max:255',
            'cities' => 'required|array|min:1',
            'cities.*' => 'exists:cities,id',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        try {
            $category = Category::findOrFail($id);
            
            $category->name_en = $request->get('name_en');
            $category->name_ar = $request->get('name_ar');
            $category->shop_id = $request->get('shop');
            
            if ($request->has('photo')) {
                // Delete old photo if exists
                if ($category->photo && file_exists(public_path($category->photo))) {
                    unlink(public_path($category->photo));
                }
                
                $the_file_path = uploadImage('assets/admin/uploads', $request->photo);
                $category->photo = $the_file_path;
            }
            
            if ($category->save()) {
                
                return redirect()->route('categories.index')->with(['success' => 'Category updated successfully']);
            } else {
                return redirect()->back()->with(['error' => 'Something went wrong']);
            }
        } catch (\Exception $ex) {
            return redirect()->back()
                ->with(['error' => 'Sorry, an error occurred: ' . $ex->getMessage()])
                ->withInput();
        }
    }
}