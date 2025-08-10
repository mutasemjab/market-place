<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\City;
use Illuminate\Http\Request;


class CityController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $cities = City::latest()->paginate(10);
        return view('admin.cities.index', compact('cities'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.cities.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name_en' => 'required|string|max:255',
            'name_ar' => 'required|string|max:255',
        ]);

       
        City::create($validated);
         
        return redirect()->route('cities.index')
            ->with('success', __('messages.city_created_successfully'));
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit(City $city)
    {
        return view('admin.cities.edit', compact('city'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, City $city)
    {
        $validated = $request->validate([
            'name_en' => 'required|string|max:255',
            'name_ar' => 'required|string|max:255',
        ]);

        $city->update($validated);

        return redirect()->route('cities.index')
            ->with('success', __('messages.city_updated_successfully'));
    }


}
