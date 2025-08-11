<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use App\Models\Offer;
use App\Models\Product;
use App\Models\Shop;
use Illuminate\Http\Request;

class OfferController extends Controller
{
    public function index()
    {
        $offers = Offer::with(['product', 'shop'])->paginate(10);
        return view('admin.offers.index', compact('offers'));
    }

    public function create()
    {
        $products = Product::all();
        $shops = Shop::all();
        return view('admin.offers.create', compact('products', 'shops'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'price' => 'required|numeric|min:0',
            'start_at' => 'required|date|after_or_equal:today',
            'expired_at' => 'required|date|after:start_at',
            'product_id' => 'nullable|exists:products,id',
            'shop_id' => 'nullable|exists:shops,id',
        ]);

        Offer::create($request->all());

        return redirect()->route('offers.index')
            ->with('success', __('messages.offer_created_successfully'));
    }

    public function show(Offer $offer)
    {
        $offer->load(['product', 'shop']);
        return view('admin.offers.show', compact('offer'));
    }

    public function edit(Offer $offer)
    {
        $products = Product::all();
        $shops = Shop::all();
        return view('admin.offers.edit', compact('offer', 'products', 'shops'));
    }

    public function update(Request $request, Offer $offer)
    {
        $request->validate([
            'price' => 'required|numeric|min:0',
            'start_at' => 'required|date',
            'expired_at' => 'required|date|after:start_at',
            'product_id' => 'nullable|exists:products,id',
            'shop_id' => 'nullable|exists:shops,id',
        ]);

        $offer->update($request->all());

        return redirect()->route('offers.index')
            ->with('success', __('messages.offer_updated_successfully'));
    }

    public function destroy(Offer $offer)
    {
        $offer->delete();

        return redirect()->route('offers.index')
            ->with('success', __('messages.offer_deleted_successfully'));
    }
}