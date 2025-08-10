<?php

namespace App\Http\Controllers\Api\v1\User;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\User;
use App\Models\Coupon;
use App\Models\CouponUser;
use App\Models\UserCoupon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class CouponController extends Controller
{

    public function applyCoupon(Request $request)
    {
        $user_id = auth()->user()->id;

        $this->validate($request, [
            'code' => 'required',
        ]);

        $couponCode = $request->input('code');

        DB::beginTransaction();
        try {
            // Step 2: Check if the coupon code exists and is valid
            $coupon = Coupon::where('code', $couponCode)
                ->where('expired_at', '>', now()) // assuming 'expired_at' is a column in your coupons table
                ->first();

            if (!$coupon) {
                throw ValidationException::withMessages(['code' => ['Invalid or expired coupon code']]);
            }

            // Step 3: Check if the coupon has already been used by the user
            $alreadyUsed = UserCoupon::where('user_id', $user_id)
                ->where('coupon_id', $coupon->id)
                ->exists();

            if ($alreadyUsed) {
                throw ValidationException::withMessages(['code' => ['Coupon code has already been used']]);
            }

            // Step 4: Apply the coupon
            $discountPercentage = $coupon->amount; // This should be a percentage value

            // Apply the discount to the user's cart
            $carts = Cart::where('user_id', $user_id)->get();

            foreach ($carts as $cart) {
                $discountAmount = ($cart->total_price * $discountPercentage) / 100;
                $cart->discount_coupon += $discountAmount;
                $cart->save();
            }

            // Mark the coupon as used for the current user
            UserCoupon::create([
                'user_id' => $user_id,
                'coupon_id' => $coupon->id,
            ]);

            DB::commit();
            return response(['message' => 'Coupon applied successfully'], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response(['error' => $e->getMessage()], 400);
        }
    }




}
