<?php

namespace App\Http\Controllers\Api\v1\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\FavoriteResource;
use App\Http\Resources\ProductResource;
use App\Models\Favorite;
use App\Models\Favourite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
class FavouriteController extends Controller
{
     public function index()
    {
        $user = Auth::guard('user-api')->user();
        
        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized'
            ], 401);
        }
        
        $favourites = $user->favourites()->with('category', 'variations', 'photos')->get();
        
        // Since these are all favourites, set is_favourite to true
        $favourites->each(function ($product) {
            $product->is_favourite = true;
        });
        
        return response()->json(['data' => $favourites]);
    }

    public function store(Request $request)
    {
        $this->validate($request,[
            'product_id'=>'required|exists:products,id'
        ]);

        $favorite = Favourite::where('user_id',$request->user()->id)
            ->where('product_id',$request->product_id)->first();
        if($favorite){
            if ($favorite->delete()) {
                return response(['message' => 'Changed','is_favorite'=>false], 200);
            }else{
                return response(['errors' => ['Something wrong']], 403);
            }
        }
        $favorite = new Favourite();
        $favorite->user_id = $request->user()->id;
        $favorite->product_id = $request->product_id;
        if ($favorite->save()) {
            return response(['message' => 'Changed','is_favorite'=>true], 200);
        }else{
            return response(['errors' => ['Something wrong']], 403);
        }
    }

}
