<?php

namespace App\Http\Controllers\Api\v1\User;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Order;
use App\Models\UserCoupon;
use Illuminate\Http\Request;
use App\Models\UserAddress;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\OrderProduct;
use App\Models\Shop;


class OrderController extends Controller
{
    /**
     * Display a listing of user's orders
     */
    public function index()
    {
        $user = auth()->user();
        
        $orders = Order::with([
            'shop',
            'address',
            'orderProducts' => function($query) {
                $query->with(['product.photos', 'variation']);
            }
        ])
        ->where('user_id', $user->id)
        ->orderBy('created_at', 'desc')
        ->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $orders->items(),
            'pagination' => [
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'per_page' => $orders->perPage(),
                'total' => $orders->total(),
            ]
        ], 200);
    }

    /**
     * Store a newly created order from cart
     */
    public function store(Request $request)
    {
        $request->validate([
            'address_id' => 'required|exists:user_addresses,id',
            'payment_type' => 'required|in:cash,card,wallet',
            'note' => 'nullable|string|max:500',
            'photo_note' => 'nullable|string', // base64 encoded image or file path
        ]);

        $user = auth()->user();

        // Get user's cart items
        $cartItems = Cart::with([
            'product' => function($query) {
                $query->with(['category.shop', 'offers']);
            },
            'variations',
            'offer'
        ])
        ->where('user_id', $user->id)
        ->where('status', 1)
        ->get();

        if ($cartItems->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Your cart is empty.'
            ], 400);
        }

        // Validate address belongs to user
        $address = UserAddress::where('id', $request->address_id)
            ->where('user_id', $user->id)
            ->first();

        if (!$address) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid address selected.'
            ], 400);
        }

        // Get shop information from first cart item
        $shopId = $cartItems->first()->shop_id;
        $shop = Shop::find($shopId);

        // Verify all cart items belong to the same shop
        foreach ($cartItems as $item) {
            if ($item->shop_id !== $shopId) {
                return response()->json([
                    'success' => false,
                    'message' => 'All cart items must belong to the same shop.'
                ], 400);
            }
        }

        try {
            DB::beginTransaction();

            // Calculate order totals
            $orderCalculation = $this->calculateOrderTotals($cartItems, $user->id);

            // Generate order number
            $orderNumber = $this->generateOrderNumber();

            // Create the order
            $order = Order::create([
                'number' => $orderNumber,
                'order_status' => 1, // Pending
                'total_taxes' => $orderCalculation['total_taxes'],
                'delivery_fee' => $orderCalculation['delivery_fee'],
                'total_prices' => $orderCalculation['total_prices'],
                'total_discounts' => $orderCalculation['total_discounts'],
                'coupon_discount' => $orderCalculation['coupon_discount'],
                'payment_type' => $request->payment_type,
                'payment_status' => 2, // Unpaid
                'date' => now(),
                'note' => $request->note,
                'photo_note' => $request->photo_note,
                'user_id' => $user->id,
                'address_id' => $request->address_id,
                'shop_id' => $shopId,
            ]);

            // Create order products
            foreach ($cartItems as $cartItem) {
                $product = $cartItem->product;
                
                // Calculate tax (assuming 16% tax rate - adjust as needed)
                $taxPercentage = 16.0;
                $totalPriceBeforeTax = $cartItem->total_price_product;
                $taxValue = ($totalPriceBeforeTax * $taxPercentage) / 100;
                $totalPriceAfterTax = $totalPriceBeforeTax + $taxValue;

                // Calculate discount values
                $originalPrice = $product->selling_price;
                $currentPrice = $cartItem->price;
                $discountPercentage = 0;
                $discountValue = 0;

                if ($cartItem->offer_id) {
                    $discountValue = ($originalPrice - $currentPrice) * $cartItem->quantity;
                    $discountPercentage = $originalPrice > 0 ? (($originalPrice - $currentPrice) / $originalPrice) * 100 : 0;
                }

                // Handle variations - create separate order product for each variation
                if ($cartItem->variations->isNotEmpty()) {
                    foreach ($cartItem->variations as $variation) {
                        OrderProduct::create([
                            'order_id' => $order->id,
                            'product_id' => $cartItem->product_id,
                            'variation_id' => $variation->id,
                            'quantity' => $cartItem->quantity,
                            'unit_price' => $cartItem->price / $cartItem->quantity,
                            'total_price_after_tax' => $totalPriceAfterTax,
                            'tax_percentage' => $taxPercentage,
                            'tax_value' => $taxValue,
                            'total_price_before_tax' => $totalPriceBeforeTax,
                            'discount_percentage' => $discountPercentage,
                            'discount_value' => $discountValue,
                            'line_discount_percentage' => null,
                            'line_discount_value' => null,
                        ]);
                    }
                } else {
                    // Create order product without variations
                    OrderProduct::create([
                        'order_id' => $order->id,
                        'product_id' => $cartItem->product_id,
                        'variation_id' => null,
                        'quantity' => $cartItem->quantity,
                        'unit_price' => $cartItem->price / $cartItem->quantity,
                        'total_price_after_tax' => $totalPriceAfterTax,
                        'tax_percentage' => $taxPercentage,
                        'tax_value' => $taxValue,
                        'total_price_before_tax' => $totalPriceBeforeTax,
                        'discount_percentage' => $discountPercentage,
                        'discount_value' => $discountValue,
                        'line_discount_percentage' => null,
                        'line_discount_value' => null,
                    ]);
                }
            }

            // Clear the cart
            $this->clearUserCart($user->id);

            // Load order with relationships for response
            $order->load([
                'shop',
                'address',
                'orderProducts' => function($query) {
                    $query->with(['product.photos', 'variation']);
                }
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $order,
                'message' => 'Order created successfully.'
            ], 200);

        } catch (\Exception $e) {
            DB::rollback();
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to create order: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified order
     */
    public function show($id)
    {
        $user = auth()->user();
        
        $order = Order::with([
            'shop',
            'address',
            'orderProducts' => function($query) {
                $query->with(['product.photos', 'variation']);
            }
        ])
        ->where('user_id', $user->id)
        ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $order
        ], 200);
    }

    /**
     * Update order status (for admin/shop owner)
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|integer|in:1,2,3,4,5,6', // 1-6 as defined in schema
        ]);

        $order = Order::findOrFail($id);
        $order->order_status = $request->status;
        $order->save();

        return response()->json([
            'success' => true,
            'data' => $order,
            'message' => 'Order status updated successfully.'
        ], 200);
    }

    /**
     * Cancel an order (only if pending or accepted)
     */
    public function cancel($id)
    {
        $user = auth()->user();
        
        $order = Order::where('user_id', $user->id)->findOrFail($id);

        // Only allow cancellation if order is pending or accepted
        if (!in_array($order->order_status, [1, 2])) {
            return response()->json([
                'success' => false,
                'message' => 'This order cannot be cancelled.'
            ], 400);
        }

        $order->order_status = 5; // Canceled
        $order->save();

        return response()->json([
            'success' => true,
            'message' => 'Order cancelled successfully.'
        ], 200);
    }

    /**
     * Get order tracking information
     */
    public function tracking($id)
    {
        $user = auth()->user();
        
        $order = Order::where('user_id', $user->id)->findOrFail($id);

        $statusText = $this->getOrderStatusText($order->order_status);

        return response()->json([
            'success' => true,
            'data' => [
                'order_number' => $order->number,
                'status' => $order->order_status,
                'status_text' => $statusText,
                'date' => $order->date,
                'estimated_delivery' => $this->getEstimatedDelivery($order),
            ]
        ], 200);
    }

    /**
     * Calculate order totals from cart items
     */
    private function calculateOrderTotals($cartItems, $userId)
    {
        $subtotal = 0;
        $totalDiscounts = 0;
        $deliveryFee = 30.0; // Set your delivery fee
        $taxRate = 16.0; // 16% tax

        // Calculate subtotal and discounts
        foreach ($cartItems as $item) {
            $subtotal += $item->total_price_product;
            
            if ($item->offer_id) {
                $originalPrice = $item->product->selling_price * $item->quantity;
                $discount = $originalPrice - $item->total_price_product;
                $totalDiscounts += $discount;
            }
        }

        // Get coupon discount
        $couponDiscount = 0;
        $userCoupons = UserCoupon::where('user_id', $userId)->with('coupon')->get();
        foreach ($userCoupons as $userCoupon) {
            if ($userCoupon->coupon && $userCoupon->coupon->expired_at > now()) {
                $couponDiscount += $userCoupon->coupon->amount;
            }
        }

        // Calculate taxes
        $totalTaxes = ($subtotal * $taxRate) / 100;
        
        // Final total
        $totalPrices = $subtotal + $totalTaxes + $deliveryFee - $couponDiscount;

        return [
            'total_taxes' => $totalTaxes,
            'delivery_fee' => $deliveryFee,
            'total_prices' => $totalPrices,
            'total_discounts' => $totalDiscounts,
            'coupon_discount' => $couponDiscount,
        ];
    }

    /**
     * Generate unique order number
     */
    private function generateOrderNumber()
    {
        $lastOrder = Order::orderBy('id', 'desc')->first();
        return $lastOrder ? $lastOrder->number + 1 : 1000;
    }

    /**
     * Clear user's cart after order creation
     */
    private function clearUserCart($userId)
    {
        // Get cart items
        $cartItems = Cart::where('user_id', $userId)->where('status', 1)->get();
        
        // Delete cart variations
        foreach ($cartItems as $cart) {
            \App\Models\CartVariation::where('cart_id', $cart->id)->delete();
        }
        
        // Delete cart items
        Cart::where('user_id', $userId)->where('status', 1)->delete();
        
        // Remove user coupons
        UserCoupon::where('user_id', $userId)->delete();
    }

    /**
     * Get order status text
     */
    private function getOrderStatusText($status)
    {
        $statuses = [
            1 => 'Pending',
            2 => 'Accepted',
            3 => 'On The Way',
            4 => 'Delivered',
            5 => 'Canceled',
            6 => 'Refund'
        ];

        return $statuses[$status] ?? 'Unknown';
    }

    /**
     * Get estimated delivery time
     */
    private function getEstimatedDelivery($order)
    {
        switch ($order->order_status) {
            case 1: // Pending
                return now()->addHours(2)->format('Y-m-d H:i:s');
            case 2: // Accepted
                return now()->addMinutes(45)->format('Y-m-d H:i:s');
            case 3: // On The Way
                return now()->addMinutes(20)->format('Y-m-d H:i:s');
            case 4: // Delivered
                return $order->updated_at->format('Y-m-d H:i:s');
            default:
                return null;
        }
    }
}
