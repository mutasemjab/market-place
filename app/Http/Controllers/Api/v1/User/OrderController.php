<?php

namespace App\Http\Controllers\Api\v1\User;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Order;
use App\Models\UserCoupon;
use App\Models\Product;
use App\Models\Variation;
use App\Models\Address;
use Illuminate\Http\Request;
use App\Http\Resources\OrderResource;
use App\Models\Setting;
use App\Models\UserAddress;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
class OrderController extends Controller
{

        public function index()
    {
        $user_id = auth()->user()->id;

        $orders = Order::with([
            'orderProducts' => function ($query) {
                $query->with([
                    'product' => function ($query) {
                        $query->with(['category', 'productImages', 'category.countries']);
                    },
                    'unit' => function ($query) {
                        $query->select('id', 'name_en', 'name_ar', 'name_fr'); // Include locale fields
                    },
                    'variation' => function ($query) {
                        $query->select('id', 'product_id', 'variation');
                    }
                ]);
            },
            'address',
            'user',
            'shop'
        ])->where('user_id', $user_id)->get();

        return response()->json(['data' => $orders]);
    }


    
    public function store(Request $request)
    {
        // Get authenticated user's ID and user type
        $user_id = auth()->user()->id;
        $user_type = auth()->user()->user_type;
    
        // Validate the request data
        $request->validate([
            'payment_type' => 'required',
            'address_id' => 'required|exists:user_addresses,id',
            'shop_id' => 'required|exists:shops,id',
            'note' => 'nullable',
            'photo_note' => 'nullable',
        ]);
    
        // Find all the cart items with status 1 for the current user
        $cartItems = Cart::where('user_id', $user_id)->where('status', 1)->get();
    
        if ($cartItems->isEmpty()) {
            return response()->json(['message' => 'No items in the cart.'], 400);
        }
    
        // Start a transaction to ensure atomicity
        DB::beginTransaction();
    
        try {
            // Initialize calculation variables
            $total_discounts = 0;
            $total_taxes = 0;
            $total_prices = 0;
            $totalPriceBeforeTaxSum = 0;
    
            // Get address and delivery fee
            $address = UserAddress::with('delivery')->findOrFail($request->input('address_id'));
            $delivery_fee = $address->delivery->price ?? 0;
    
            // Calculate totals from cart items
            foreach ($cartItems as $cartItem) {
                $total_discounts += $cartItem->discount_coupon ?? 0;
                $total_taxes += $cartItem->product->tax ?? 0;
                $total_prices += $cartItem->total_price_product;
            }
    
            // Minimum order check based on user type
            $min_order = Setting::first();
            if (($user_type == 1 && $total_prices < $min_order->min_order) || ($user_type != 1 && $total_prices < $min_order->min_order_wholeSale)) {
                $minOrderAmount = $user_type == 1 ? $min_order->min_order : $min_order->min_order_wholeSale;
                DB::rollBack();
                $response = response()->json(['message' => 'Your order is less than the minimum order. Please buy at least ' . $minOrderAmount], 404);
                Log::info('Order Response', ['response' => $response->getContent()]);
                return $response;
            }
    
            // Generate the order number based on order type, ensuring uniqueness
            $lastOrder = Order::where('order_type', 1)->orderBy('number', 'desc')->lockForUpdate()->first();
            $newNumber = $lastOrder ? $lastOrder->number + 1 : 1;
    
            $lastOrderForRefund = Order::where('order_type', 2)->orderBy('number', 'desc')->lockForUpdate()->first();
            $newNumberOfRefund = $lastOrderForRefund ? $lastOrderForRefund->number + 1 : 1;
    
            $orderNumber = $request->order_type == 2 ? $newNumberOfRefund : $newNumber;
    
            // Create a new order
            $order = new Order([
                'number' => $orderNumber,
                'address_id' => $request->input('address_id'),
                'payment_type' => $request->input('payment_type'),
                'note' => $request->input('note'),
                'total_discounts' => $total_discounts,
                'coupon_discount' => $total_discounts, // Store the coupon discount
                'delivery_fee' => $delivery_fee,
                'total_taxes' => $total_taxes,
                'total_prices' => $total_prices + $delivery_fee,
                'date' => now(),
                'user_id' => $user_id,
                'shop_id' => $request->input('shop_id'),
            ]);
    
            // Handle photo note upload
            if ($request->hasFile('photo_note')) {
                $the_file_path = uploadImage('assets/admin/uploads', $request->file('photo_note'));
                $order->photo_note = $the_file_path;
            }
            $order->save();
    
            // Calculate totals for cart items
            foreach ($cartItems as $cartItem) {
                $total_price_after_tax_for_result = $cartItem->price * $cartItem->quantity;
                $total_price_before_tax_for_result = $total_price_after_tax_for_result / (1 + ($cartItem->product->tax / 100));
                $totalPriceBeforeTaxSum += $total_price_before_tax_for_result;
            }
    
            $final_result_total_price_before_tax = $totalPriceBeforeTaxSum;
            $discount_percentage = $total_discounts / $final_result_total_price_before_tax;
    
            // Attach cart items to the order
            foreach ($cartItems as $cartItem) {
                $unit_id = ($user_type == 1) ? $cartItem->product->unit->id : $cartItem->product->units()->first()->id;
    
                $total_price_after_tax = $cartItem->price * $cartItem->quantity;
                $total_price_before_tax = $total_price_after_tax / (1 + ($cartItem->product->tax / 100));
    
                $order->products()->attach($cartItem->product_id, [
                    'quantity' => $cartItem->quantity,
                    'unit_price' => $cartItem->price,
                    'variation_id' => $cartItem->variation_id,
                    'unit_id' => $unit_id,
                    'total_price_after_tax' => $total_price_after_tax,
                    'total_price_before_tax' => $total_price_before_tax,
                    'tax_percentage' => $cartItem->product->tax,
                    'discount_percentage' => $discount_percentage,
                    'discount_value' => $discount_percentage * $total_price_before_tax,
                    'line_discount_percentage' => null,
                    'line_discount_value' => null,
                    'tax_value' => ($total_price_before_tax - ($discount_percentage * $total_price_before_tax)) * ($cartItem->product->tax / 100),
                ]);
            }
    
            // Update cart status to processed (status 2)
            Cart::where('user_id', $user_id)->where('status', 1)->update([
                'status' => 2,
            ]);
    
            // Delete user coupon after the order is completed
            $userCoupon = UserCoupon::where('user_id', $user_id)->first();
            if ($userCoupon) {
                $userCoupon->delete();
            }
    
            // Commit the transaction
            DB::commit();
    
            // Log the response and return success
            $response = response()->json($order, 200);
            Log::info('Order Response', ['response' => $response->getContent()]);
            return $response;
    
        } catch (\Exception $e) {
            // Rollback the transaction in case of an error
            DB::rollBack();
            Log::error('Order Creation Error', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Order creation failed', 'error' => $e->getMessage()], 500);
        }
    }
    


      public function cancel_order($id)
      {
          // Find the order by ID
          $order = Order::find($id);

          // Check if the order exists
          if (!$order) {
              return response()->json(['message' => 'Order not found'], 404);
          }

          // Check if the order is cancellable (you may need to add additional logic here)
          if ($order->order_status == 1 ) {
              // Update the order status to cancelled
              $order->update(['order_status' => 5]);


              return response()->json(['message' => 'Order cancelled successfully'], 200);
          } else {
              return response()->json(['message' => 'Order is already cancelled'], 422);
          }
      }




}
