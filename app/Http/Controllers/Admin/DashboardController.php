<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Teacher;
use App\Models\Clas; // Replace with your actual class model name
use App\Models\Driver;
use App\Models\Product;
use App\Models\Provider;

class DashboardController extends Controller
{
    public function index()
    {
        $categories = Category::count();
        $products = Product::count();
    
        return view('admin.dashboard', compact('categories', 'products',));
    }
}
