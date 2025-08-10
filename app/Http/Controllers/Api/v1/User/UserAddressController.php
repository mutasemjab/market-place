<?php

namespace App\Http\Controllers\Api\v1\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserAddress;
use App\Http\Resources\AddressResource;
use App\Models\Address;
use App\Models\Order;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UserAddressController extends Controller
{
    public function index(Request $request)
    {

        $user_id = $request->user()->id;
        $address = UserAddress::where('user_id', $user_id)->get();
        return response()->json(['data' => $address]);
    }

    public function create() {}


    public function store(Request $request)
    {
        $user_id = auth()->user()->id;
        $this->validate($request, [
            'lng' => 'required',
            'lat' => 'required',
            'address' => 'required',

        ]);

        DB::beginTransaction();
        try {
            if ($address = UserAddress::where('user_id', $user_id)->where(function ($query) use ($request) {
                $query->where('lng', $request->lng)
                    ->where('lat', $request->lat);
            })->first()) {
            } else {
                $address = new UserAddress();
            }
            $address->lng = $request->lng;
            $address->lat = $request->lat;
            $address->address = $request->address;
            $address->user_id = $user_id;
            $address->save();
            DB::commit();
            return response(['message' => 'Address added'], 200);
        } catch (Exception $e) {
            DB::rollBack();
            Log::info($e->getMessage());
            return response(['errors' => $e->getMessage()], 403);
        }
    }


    public function update(Request $request, $address_id)
    {

        $userAddress = UserAddress::findOrFail($address_id);
        $userAddress->lng = $request->lng ?? $userAddress->lng;
        $userAddress->lat = $request->lat ?? $userAddress->lat;
        $userAddress->address = $request->address ?? $userAddress->address;

        if ($userAddress->save()) {
            return response(['message' => ['Your address has been changed']]);
        } else {
            return response(['errors' => ['There is something wrong']], 402);
        }
    }


    public function destroy($id)
    {
        $userAddress = UserAddress::find($id);

        // Check if there are any orders associated with this address
        $orderExists = Order::where('address_id', $id)->exists();

        if ($orderExists) {
            return response(['errors' => ['This address is associated with an order and cannot be deleted']], 403);
        }

        if ($userAddress->delete()) {
            return response(['message' => 'Address is deleted'], 200);
        } else {
            return response(['errors' => ['Something went wrong']], 403);
        }
    }
}
