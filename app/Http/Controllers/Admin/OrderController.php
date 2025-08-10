<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NoteVoucher;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\Product;
use App\Models\Shop;
use App\Models\Unit;
use App\Models\User;
use App\Models\UserAddress;
use App\Models\Warehouse;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    
    public function index(Request $request)
    {
        
        
        $query = Order::query();
        
        // Apply search filter if present in the request
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('number', 'LIKE', "%$search%");
            });
            
            // Get all results without pagination when searching
            $data = $query->orderBy('created_at', 'desc')->get();
        } else {
            // Use pagination for non-search views
            $data = $query->orderBy('created_at', 'desc')->paginate(PAGINATION_COUNT);
        }
        
        return view('admin.orders.index', compact('data'));
    }
    

    public function create()
    {
        $products = Product::get();
        $users = User::get();
        $shops = Shop::get();
        return view('admin.orders.create', compact('products','users','shops','warehouses'));
    }

    

    public function store(Request $request)
    {
        
        // Validate the request data
        $validatedData = $request->validate([
            'order_type' => 'required|integer',
            'date' => 'required|date',
            'shop' => 'required|integer|exists:shops,id',
            'payment_type' => 'required|string',
            'address' => 'required|integer|exists:user_addresses,id',
            'products' => 'required|array',
            'products.*.name' => 'required|string',
            'products.*.unit' => 'required|integer|exists:units,id',
            'products.*.quantity' => 'required|integer|min:1',
            'products.*.selling_price_without_tax' => 'required|numeric',
            'products.*.selling_price_with_tax' => 'required|numeric',
            'products.*.tax' => 'required|numeric',
            'products.*.discount_fixed' => 'nullable|numeric',
            'products.*.discount_percentage' => 'nullable|numeric',
            'coupon_discount' => 'nullable|numeric|min:0|max:100'
        ]);

        // Start a transaction to prevent duplicates
        DB::beginTransaction();

        try {
                // Lock the order table for update to avoid race conditions
                $lastOrder = Order::where('order_type', $request->order_type)
                ->orderByDesc('number')
                ->lockForUpdate()
                ->first();


            
            // Generate the unique order number
            $newOrderNumber = $lastOrder ? $lastOrder->number + 1 : 1;
        //  return $newOrderNumber;
            // Check for existing order with the same number and order type
            $existingOrder = Order::where('order_type', $request->order_type)
                                ->where('number', $newOrderNumber)
                                ->first();

            if ($existingOrder) {
                DB::rollBack();
                return response()->json(['message' => 'Duplicate order detected.'], 409);
            }

            // Define the order status
            $orderStatus = $request->order_type == 2 ? 6 : 1;
            $address = UserAddress::find($request->address);
            $deliveryFee = doubleval($address->delivery->price ?? 0);

            $user = User::where('name', $request->user)->first();
            
            // Create the order
            $order = Order::create([
                'number' => $newOrderNumber,
                'order_status' => $orderStatus,
                'total_taxes' => 0,
                'delivery_fee' => $deliveryFee,
                'total_prices' => 0,
                'total_discounts' => 0,
                'payment_type' => $request->payment_type,
                'payment_status' => 2,
                'order_type' => $request->order_type,
                'date' => Carbon::parse($request->date),
                'user_id' => $user->id,
                'address_id' => $request->address,
                'shop_id' => $request->shop,
                'coupon_discount' => $request->coupon_discount ?? 0,
            ]);

            // Initialize totals
            $totalTaxes = 0;
            $totalPrices = 0;
            $totalDiscounts = 0;
            $couponDiscountPercentage = $request->coupon_discount ?? 0;

            // Process each product in the order
            foreach ($request->products as $productData) {
                $product = Product::where('name_ar', $productData['name'])->first();

                if ($product) {
                    $quantity = $productData['quantity'];
                    $unitPriceWithoutTax = $productData['selling_price_without_tax'];
                    $taxPercentage = $productData['tax'];
                    $unitPriceWithTax = $unitPriceWithoutTax * (1 + $taxPercentage / 100);

                    $totalPriceBeforeTax = $unitPriceWithoutTax * $quantity;
                    $totalPriceAfterTax = $unitPriceWithTax * $quantity;

                    $lineDiscountFixed = $productData['discount_fixed'] ?? 0;
                    $lineDiscountPercentage = $productData['discount_percentage'] ?? 0;
                    $lineDiscountValue = ($totalPriceBeforeTax * $lineDiscountPercentage / 100) + $lineDiscountFixed;
                    $totalPriceAfterLineDiscount = $totalPriceBeforeTax - $lineDiscountValue;

                    $couponDiscountValue = $totalPriceAfterLineDiscount * ($couponDiscountPercentage / 100);
                    $totalPriceAfterAllDiscounts = $totalPriceAfterLineDiscount - $couponDiscountValue;

                    $totalRowTax = $totalPriceAfterAllDiscounts * ($taxPercentage / 100);

                    OrderProduct::create([
                        'unit_id' => $productData['unit'],
                        'order_id' => $order->id,
                        'product_id' => $product->id,
                        'variation_id' => null,
                        'quantity' => $quantity,
                        'unit_price' => $unitPriceWithTax,
                        'total_price_after_tax' => $totalPriceAfterAllDiscounts + $totalRowTax,
                        'tax_percentage' => $taxPercentage,
                        'tax_value' => $totalRowTax,
                        'total_price_before_tax' => $totalPriceBeforeTax,
                        'line_discount_percentage' => $lineDiscountPercentage,
                        'line_discount_value' => $lineDiscountValue,
                        'discount_value' => $couponDiscountValue,
                    ]);

                    // Accumulate totals
                    $totalTaxes += $totalRowTax;
                    $totalPrices += $totalPriceAfterAllDiscounts + $totalRowTax;
                    $totalDiscounts += $lineDiscountValue + $couponDiscountValue;
                }
            }

            // Update the order with the calculated totals
            $order->update([
                'total_taxes' => $totalTaxes,
                'total_prices' => $totalPrices + $deliveryFee,
                'total_discounts' => $totalDiscounts,
            ]);

            // Handle refund logic
            if ($order->order_type == 2) {
                $lastNoteVoucher = NoteVoucher::orderBy('id', 'desc')->first();
                $newVoucherNumber = $lastNoteVoucher ? $lastNoteVoucher->id + 1 : 1;

                // Create the note voucher
                $noteVoucher = NoteVoucher::create([
                    'note_voucher_type_id' => 1,
                    'date_note_voucher' => $order->date,
                    'number' => $newVoucherNumber,
                    'from_warehouse_id' => $order->shop->warehouse->id ?? 1,
                    'to_warehouse_id' => $request['toWarehouse'] ?? null,
                    'shop_id' => $order->shop->id,
                    'order_id' => $order->id,
                    'note' => "فاتورة مرتجع رقم " . (string)$order->number,
                ]);

                // Attach products to the voucher
                foreach ($request['products'] as $productData) {
                    $product = Product::where('name_ar', $productData['name'])->firstOrFail();

                    $noteVoucher->voucherProducts()->attach($product->id, [
                        'unit_id' => $productData['unit'],
                        'quantity' => $productData['quantity'],
                        'purchasing_price' => $productData['purchasing_price'] ?? null,
                        'note' => $productData['note'] ?? null,
                    ]);
                }
            }

            // Commit the transaction
            DB::commit();

            // Redirect to the appropriate page
            if ($request->redirect_to == 'index') {
                return redirect()->route('orders.index')->with('success', 'Order created successfully.');
            } else {
                return redirect()->route('orders.show', $order->id)->with('success', 'Order created successfully.');
            }

        } catch (\Exception $e) {
            // Rollback in case of an error
            DB::rollBack();
            return response()->json(['message' => 'Order creation failed', 'error' => $e->getMessage()], 500);
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
        $order = Order::with('products', 'products.variations')->findOrFail($id);
        return view('admin.orders.show', compact('order'));

    }

    public function edit($id)
    {
        $order = Order::with(['products.units', 'products.unit', 'user.addresses'])->findOrFail($id);
        $shops = Shop::all();
        return view('admin.orders.edit', compact('order', 'shops'));
    }

  

    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'date' => 'required|date',
            'payment_type' => 'required|string',
            'products' => 'required|array',
            'products.*.name' => 'required|string',
            'products.*.unit' => 'required|integer|exists:units,id',
            'products.*.quantity' => 'required|integer|min:1',
            'products.*.selling_price_without_tax' => 'required|numeric',
            'products.*.selling_price_with_tax' => 'required|numeric',
            'products.*.tax' => 'required|numeric',
            'products.*.line_discount_fixed' => 'nullable|numeric',
            'products.*.line_discount_percentage' => 'nullable|numeric',
            'coupon_discount' => 'nullable|numeric|min:0|max:100'
        ]);
    

        $order = Order::findOrFail($id);
        $order->update([
            'order_status' => $request->order_status,
            'date' => Carbon::parse($request->date),
            'shop_id' => $request->shop,
            'payment_type' => $request->payment_type,
            'address_id' => $request->address,
            'user_id' => User::where('name', $request->user)->first()->id,
            'coupon_discount' => $request->coupon_discount ?? 0, // Ensure coupon_discount is saved, defaulting to 0
        ]);

        // Reset totals
        $totalTaxes = 0;
        $totalPrices = 0;
        $totalDiscounts = 0;
        $totalBeforeTax = 0;
        $couponDiscountPercentage = $request->coupon_discount ?? 0; // Get coupon discount
        $couponDiscountAmount = 0; // Initialize coupon discount amount

        // Detach old products
        $order->products()->detach();

        // Attach new products
        foreach ($request->products as $productData) {
            $product = Product::where('name_ar', $productData['name'])->first();
        
            if ($product) {
                $quantity = $productData['quantity'];
                $unitPriceWithoutTax = $productData['selling_price_without_tax'];
                $taxPercentage = $productData['tax'];
                $unitPriceWithTax = $unitPriceWithoutTax * (1 + $taxPercentage / 100);
        
                $totalPriceBeforeTax = $unitPriceWithoutTax * $quantity;
                $totalBeforeTax += $totalPriceBeforeTax;
        
                // Get new discount inputs from the form
                $lineDiscountPercentage = isset($productData['line_discount_percentage']) ? (float)$productData['line_discount_percentage'] : 0;
                $lineDiscountValue = isset($productData['line_discount_fixed']) ? (float)$productData['line_discount_fixed'] : 0;
        
                // Get the original discount values for comparison
                $originalLineDiscountValue = isset($productData['original_line_discount_value']) ? (float)$productData['original_line_discount_value'] : 0;
                $originalLineDiscountPercentage = isset($productData['original_line_discount_percentage']) ? (float)$productData['original_line_discount_percentage'] : 0;
        
                // Fix: Properly handle the transition between percentage and fixed discounts
                // Always recalculate based on the current inputs to prevent compounding
                
                // If we have a percentage, calculate the percentage discount
                if ($lineDiscountPercentage > 0) {
                    // Apply percentage discount
                    $lineDiscountValue = ($totalPriceBeforeTax * $lineDiscountPercentage / 100);
                    
                    // If we also have a fixed value that was manually entered (i.e., it doesn't match the calculated percentage value)
                    if ($lineDiscountValue != $originalLineDiscountValue && $lineDiscountPercentage == $originalLineDiscountPercentage) {
                        // Use the manually entered fixed value instead
                        $lineDiscountValue = (float)$productData['line_discount_fixed'];
                    }
                } 
                // No percentage but we have a fixed value
                else if ($lineDiscountValue > 0) {
                    // Use just the fixed value
                    // (lineDiscountValue is already set correctly)
                }
                // No discount
                else {
                    $lineDiscountValue = 0;
                }
        
                // Calculate totals after applying the line discount
                $totalPriceAfterLineDiscount = $totalPriceBeforeTax - $lineDiscountValue;
                
                // Add this line's discount to the total discounts
                $totalDiscounts += $lineDiscountValue;
        
                // Calculate tax after discounts
                $totalRowTax = $totalPriceAfterLineDiscount * ($taxPercentage / 100);
                $totalTaxes += $totalRowTax;
                
                // Calculate total price after tax
                $totalPriceAfterTax = $totalPriceAfterLineDiscount + $totalRowTax;
        
                // Attach the product to the order with the correct discount and tax values
                $order->products()->attach($product->id, [
                    'unit_id' => $productData['unit'],
                    'variation_id' => null,
                    'quantity' => $quantity,
                    'unit_price' => $unitPriceWithTax,
                    'total_price_after_tax' => $totalPriceAfterTax,
                    'tax_percentage' => $taxPercentage,
                    'tax_value' => $totalRowTax,
                    'total_price_before_tax' => $totalPriceBeforeTax,
                    'line_discount_percentage' => $lineDiscountPercentage,
                    'line_discount_value' => $lineDiscountValue,
                ]);
        
                // Accumulate total prices
                $totalPrices += $totalPriceAfterTax;
            }
        }
        
        // Calculate coupon discount amount based on total prices before tax but after line discounts
        $subtotalAfterLineDiscounts = $totalBeforeTax - $totalDiscounts;
        $couponDiscountAmount = $subtotalAfterLineDiscounts * ($couponDiscountPercentage / 100);
        
        // Apply coupon discount to the total prices
        $totalPrices -= $couponDiscountAmount;
        
        // Add delivery fee to total prices
        $totalPrices += $order->delivery_fee;
        
        // Update order totals
        $order->update([
            'total_taxes' => $totalTaxes,
            'total_prices' => $totalPrices,
            'total_discounts' => $totalDiscounts + $couponDiscountAmount, // Include coupon discount in total discounts
        ]);

      
        // Success message - include invoice information if relevant
        $successMessage = 'Order updated successfully.';
  
        
        return redirect()->route('orders.index')->with('success', $successMessage);
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
            // Find the order by ID
            $order = Order::findOrFail($id);

            // Detach all related products from the order
            $order->products()->detach();

            // Delete the order
            $order->delete();

            // Redirect with success message
            return redirect()->route('orders.index')->with('success', 'Order and associated NoteVoucher deleted successfully.');
        } catch (\Exception $e) {
            // Redirect with error message
            return redirect()->route('orders.index')->with('error', 'Error deleting order: ' . $e->getMessage());
        }
    }

 


}
