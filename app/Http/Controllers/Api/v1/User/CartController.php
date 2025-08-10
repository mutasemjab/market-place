<?php

namespace App\Http\Controllers\Api\v1\User;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Product;
use App\Models\CartVariation;
use App\Models\UserCoupon;
use Illuminate\Http\Request;
use App\Http\Resources\CartResource;
use Illuminate\Support\Facades\Auth;


class CartController extends Controller
{

    public function index()
    {
        $user = auth()->user();
    
        // Get user's cart items with relationships
        $carts = Cart::with([
            'product' => function ($query) {
                $query->with(['photos','category' => function($q) {
                    $q->with('shop');
                }, 'variations', 'offers']);
            },
            'variations', // Load selected variations for each cart item
            'offer' // Load the offer if applied
        ])->where('user_id', $user->id)
          ->where('status', 1)
          ->get();
    
        $total = 0;
        $totalDiscount = 0;
        $currency = 'JD'; // Set your default currency
        $shopId = null;
        $shopInfo = null;
    
        foreach ($carts as $cart) {
            $product = $cart->product;
            
            // Get shop info from first cart item
            if (!$shopId && $product->category) {
                $shopId = $product->category->shop_id;
                $shopInfo = $product->category->shop;
            }
    
            // Check if product has active offers
            $activeOffer = $product->offers()
                ->where('start_at', '<=', now())
                ->where('expired_at', '>', now())
                ->first();
    
            // Set offer information
            $product->has_offer = $activeOffer ? true : false;
            $product->offer_price = $activeOffer ? $activeOffer->price : null;
    
            // Set product information for response
            $product->current_price = $cart->price; // Use price stored in cart
            $product->selected_variations = $cart->variations; // Show selected variations
            
            // Check if user has this product in favourites (if you have favourites table)
            // $product->is_favourite = $user->favourites()->where('product_id', $product->id)->exists();
            $product->is_favourite = false; // Set to false if no favourites system
    
            // Calculate totals
            $total += $cart->total_price_product;
    
            // Calculate discount if there's an offer applied
            if ($cart->offer_id && $activeOffer) {
                $originalPrice = $product->selling_price;
                $variationPrice = $cart->variations->sum('price');
                $fullOriginalPrice = ($originalPrice + $variationPrice) * $cart->quantity;
                $discount = $fullOriginalPrice - $cart->total_price_product;
                $totalDiscount += $discount;
            }
        }
    
        // Apply coupon discount if you have coupon system
        $couponDiscount = $this->applyCouponDiscount($user->id, $total);
        $totalDiscount += $couponDiscount;
        $totalAfterDiscounts = $total - $couponDiscount;
    
        // Update cart records with coupon discount if applied
        if ($couponDiscount > 0) {
            foreach ($carts as $cart) {
                $cart->discount_coupon = $couponDiscount / $carts->count(); // Distribute equally
                $cart->save();
            }
        }
    
        return response()->json([
            'success' => true,
            'data' => $carts->map(function ($cart) {
                return [
                    'id' => $cart->id,
                    'shop_id' => $cart->shop_id,
                    'product' => [
                        'id' => $cart->product->id,
                        'name_en' => $cart->product->name_en,
                        'name_ar' => $cart->product->name_ar,
                        'description_en' => $cart->product->description_en,
                        'description_ar' => $cart->product->description_ar,
                        'selling_price' => $cart->product->selling_price,
                        'current_price' => $cart->price,
                        'has_offer' => $cart->product->has_offer,
                        'offer_price' => $cart->product->offer_price,
                        'is_favourite' => $cart->product->is_favourite,
                        'category' => $cart->product->category,
                        'variations' => $cart->product->variations,
                        'photos' => $cart->product->photos,
                    ],
                    'selected_variations' => $cart->variations,
                    'quantity' => $cart->quantity,
                    'price' => $cart->price,
                    'total_price_product' => $cart->total_price_product,
                    'discount_coupon' => $cart->discount_coupon,
                ];
            }),
            'summary' => [
                'total' => $total,
                'total_discount' => $totalDiscount,
                'coupon_discount' => $couponDiscount,
                'total_after_discounts' => $totalAfterDiscounts,
                'currency' => $currency,
                'items_count' => $carts->count(),
                'shop_id' => $shopId,
                'shop_info' => $shopInfo,
            ]
        ], 200);
    }

    
   private function applyCouponDiscount($userId, $total)
    {
        $couponDiscount = 0;
    
        // Fetch applied coupons
        $userCoupons = UserCoupon::where('user_id', $userId)->with('coupon')->get();
    
        foreach ($userCoupons as $userCoupon) {
            $coupon = $userCoupon->coupon;
    
            if ($coupon && $coupon->expired_at > now()) {
                // Calculate the discount as a percentage of the total
                $couponDiscount += $coupon->amount;
            }
        }
    
        return $couponDiscount;
    }

    public function store(Request $request)
    {
        // Validate the request data
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'variation_ids' => 'nullable|array',
            'variation_ids.*' => 'exists:product_variations,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $user = auth()->user();
        $product = Product::with('category.shop')->findOrFail($request->input('product_id'));
        $quantity = $request->input('quantity', 1);
        $variationIds = $request->input('variation_ids', []);

        // Get the shop_id from the product's category
        $shopId = $product->category->shop_id;

        // Check if user already has items from a different shop in cart
        $existingCartWithDifferentShop = Cart::where('user_id', $user->id)
            ->where('status', 1)
            ->where('shop_id', '!=', $shopId)
            ->first();

        if ($existingCartWithDifferentShop) {
            return response()->json([
                'success' => false,
                'message' => 'You can only add products from the same shop. Please clear your cart or complete your current order first.',
                'current_shop_id' => $existingCartWithDifferentShop->shop_id,
                'requested_shop_id' => $shopId,
            ], 400);
        }

        // Calculate price based on offers and variations
        $basePrice = $product->selling_price;
        
        // Check for active offers
        $offer = $product->offers()->where('expired_at', '>', now())->first();
        if ($offer) {
            $basePrice = $offer->price;
        }

        // Add variation prices if any
        $variationPrice = 0;
        if (!empty($variationIds)) {
            $variations = ProductVariation::whereIn('id', $variationIds)->get();
            $variationPrice = $variations->sum('price');
        }

        $finalPrice = $basePrice + $variationPrice;

        // Check if similar cart item exists (same product and variations)
        $existingCart = Cart::where('user_id', $user->id)
            ->where('product_id', $request->input('product_id'))
            ->where('shop_id', $shopId)
            ->where('status', 1)
            ->first();

        // If existing cart has same variations, update quantity
        if ($existingCart && $this->hasSameVariations($existingCart, $variationIds)) {
            $existingCart->quantity += $quantity;
            $existingCart->total_price_product = $finalPrice * $existingCart->quantity;
            $existingCart->save();

            return response()->json([
                'success' => true,
                'data' => $existingCart,
                'message' => 'Cart item quantity updated successfully.'
            ], 200);
        }

        // Create new cart item
        $cart = Cart::create([
            'user_id' => $user->id,
            'product_id' => $request->input('product_id'),
            'shop_id' => $shopId,
            'offer_id' => $offer ? $offer->id : null,
            'quantity' => $quantity,
            'price' => $finalPrice,
            'total_price_product' => $finalPrice * $quantity,
            'status' => 1,
        ]);

        // Add variations to cart_variations table
        if (!empty($variationIds)) {
            foreach ($variationIds as $variationId) {
                CartVariation::create([
                    'cart_id' => $cart->id,
                    'variation_id' => $variationId,
                ]);
            }
        }

        // Load relationships for response
        $cart->load(['product', 'variations']);

        return response()->json([
            'success' => true,
            'data' => $cart,
            'message' => 'Product added to cart successfully.'
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        $cart = Cart::where('user_id', auth()->id())->findOrFail($id);
        
        $cart->quantity = $request->input('quantity');
        $cart->total_price_product = $cart->price * $cart->quantity;
        $cart->save();

        return response()->json([
            'success' => true,
            'data' => $cart,
            'message' => 'Cart item updated successfully.'
        ], 200);
    }

    public function destroy($id)
    {
        $cart = Cart::where('user_id', auth()->id())->findOrFail($id);
        
        // Delete related cart variations
        CartVariation::where('cart_id', $cart->id)->delete();
        
        // Delete the cart item
        $cart->delete();

        // Check if user has any remaining cart items, if not, remove coupon
        $remainingCartItems = Cart::where('user_id', auth()->id())->where('status', 1)->count();
        if ($remainingCartItems == 0) {
            $userCoupon = UserCoupon::where('user_id', auth()->id())->first();
            if ($userCoupon) {
                $userCoupon->delete();
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Item removed from cart successfully.'
        ], 200);
    }

    /**
     * Clear all cart items for the current user
     */
    public function clearCart()
    {
        $user = auth()->user();
        
        // Get all cart items for the user
        $cartItems = Cart::where('user_id', $user->id)->where('status', 1)->get();
        
        // Delete all cart variations
        foreach ($cartItems as $cart) {
            CartVariation::where('cart_id', $cart->id)->delete();
        }
        
        // Delete all cart items
        Cart::where('user_id', $user->id)->where('status', 1)->delete();
        
        // Remove user coupons
        UserCoupon::where('user_id', $user->id)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Cart cleared successfully.'
        ], 200);
    }

    private function hasSameVariations($cart, $variationIds)
    {
        $existingVariationIds = $cart->variations->pluck('id')->sort()->values()->toArray();
        $newVariationIds = collect($variationIds)->sort()->values()->toArray();
        
        return $existingVariationIds === $newVariationIds;
    }
}
