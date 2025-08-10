<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Offer;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class OfferController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data= Offer::where('shop_id',auth()->user()->shop_id)->paginate(PAGINATION_COUNT);

        return view('admin.offers.index',compact('data'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $products = Product::get();
        return view('admin.offers.create',compact('products'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try{
            $offer = new Offer();

            $offer->price = $request->get('price');
            $offer->start_at = $request->get('start_at');
            $offer->expired_at = $request->get('expired_at');
            $offer->user_type = $request->get('user_type');
            $offer->product_id = $request->input('product');
            $offer->shop_id = auth()->user()->shop_id;

            if($offer->save())
            {
                return redirect()->route('offers.index')->with(['success' => 'Offer created']);
            }else{
                return redirect()->back()->with(['error' => 'Something wrong']);
            }

        }catch(\Exception $ex){
           Log::error($ex);
           return redirect()->back()
            ->with(['error' => 'An error occurred: ' . $ex->getMessage()])
            ->withInput();
        }

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $data = Offer::findOrFail($id); // Retrieve the category by ID
        $products = Product::all();
        return view('admin.offers.edit', ['products' => $products,'data' => $data]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
        {
            try {
                // Find the offer by ID
                $offer = Offer::findOrFail($id);

                // Update offer fields
                $offer->price = $request->get('price');
                $offer->start_at = $request->get('start_at');
                $offer->expired_at = $request->get('expired_at');
                $offer->user_type = $request->get('user_type');
                $offer->product_id = $request->input('product');

                // Save the updated offer
                if ($offer->save()) {
                    return redirect()->route('offers.index')->with(['success' => 'Offer updated']);
                } else {
                    return redirect()->back()->with(['error' => 'Something went wrong while updating the offer']);
                }
            } catch (\Exception $ex) {
                Log::error($ex);
                return redirect()->back()
                    ->with(['error' => 'An error occurred: ' . $ex->getMessage()])
                    ->withInput();
            }
        }


    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
       try {

            $item_row = Offer::select("id")->where('id','=',$id)->first();

            if (!empty($item_row)) {

        $flag = Offer::where('id','=',$id)->delete();

        if ($flag) {
            return redirect()->back()
            ->with(['success' => '   Delete Succefully   ']);
            } else {
            return redirect()->back()
            ->with(['error' => '   Something Wrong']);
            }

            } else {
            return redirect()->back()
            ->with(['error' => '   cant reach fo this data   ']);
            }

       } catch (\Exception $ex) {

            return redirect()->back()
            ->with(['error' => ' Something Wrong   ' . $ex->getMessage()]);
            }
    }
}
