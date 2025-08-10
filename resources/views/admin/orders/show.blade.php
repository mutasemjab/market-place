@extends('layouts.admin')
@section('title')
Orders
@endsection

@section('css')
<style>
    body {
        font-family: Arial, sans-serif;
        margin: 0;
        padding: 0;
    }

    #invoice {
        width: 100%;
        max-width: 100%;
        margin: auto;
        border: 1px solid #ccc;
        padding: 20px;
        background-color: #fff;
        box-sizing: border-box;
    }

    .custom_photo {
        width: 50px;
        height: 35px;
        object-fit: cover;
    }

    #header {
        display: flex;
        justify-content: center;
        align-items: center;
        flex-direction: column;
        margin-bottom: -50px;
    }

    #logo {
        max-width: 100px;
        margin-bottom: 10px;
        margin: 0 auto;
    }

    #company-name {
        font-size: 1.5em;
        font-weight: bold;
        text-align: center;
        margin-bottom: 10px;
    }

@if(app()->getLocale() == 'ar')

    #details {
    display: flex;
    justify-content: space-between; 
    align-items: flex-start; 
    margin-top: 20px;
    direction: ltr; 
    }

    #details-left {
        flex-basis: 48%;
        text-align: right; 
    }

    #details-right {
        flex-basis: 48%;
        text-align: left; 
    }
    
    #details-left p,
    #details-right p {
        margin: 0; /* Removes all margins around the paragraphs */
        margin-bottom: 5px; /* Adds a smaller bottom margin to create a bit of space */
    }
@else
    #details {
    display: flex;
    justify-content: space-between; 
    align-items: flex-start; 
    margin-top: 20px;
    direction: ltr;
    }

    #details-left {
        text-align: left;
        flex-basis: 48%;
    }

    #details-right {
        text-align: right;
        flex-basis: 48%;
    }
    #details-left p,
#details-right p {
    margin: 0; /* Removes all margins around the paragraphs */
    margin-bottom: 5px; /* Adds a smaller bottom margin to create a bit of space */
}
@endif



    p.inoice-d-address {
    direction: ltr !important;
}


    #client-details {
        margin-top: 30px;
    }

    #client-details p {
        text-align: left;
        margin: 0;
    }

    #products {
        margin-top: 30px;
        width: 100%;
        border-collapse: collapse;
        table-layout: auto; /* Allows columns to adjust based on content */
    }

    #products th, #products td {
        border: 1px solid #ddd;
        padding: 4px;
        text-align: center;
        box-sizing: border-box;
        word-wrap: break-word;
        font-size: 0.8em;
    }

    #products th {
        background-color: #f5f5f5;
        font-weight: bold;
    }

    #totals {
        margin-top: 20px;
        text-align: left;
        font-size: 1.2em;
    }

    #totals div {
        margin-bottom: 10px;
    }

    @page {
        size: A4 landscape; 
        margin: 5mm;
    }

    @media print {
        body, html {
            width: 297mm; 
            height: 210mm; 
            margin: 0;
            padding: 0;
        }

        #invoice {
            width: 100%;
            margin: 0 auto;
            padding: 20px;
            border: none;
            background-color: #fff;
            box-sizing: border-box;
        }

        #products th, #products td {
            font-size: 0.75em;
            padding: 3px; /* Further reduced padding for printing */
        }

        tr, td {
            page-break-inside: avoid;
        }

        .print-hidden, .btn, .navbar, .footer, header, footer {
            display: none !important;
        }
    }
</style>
@endsection

@section('content')
<button onclick="printInvoice()" class="btn btn-sm btn-danger print-hidden">Print Invoice</button>

@if($order->qr_code == null)
<a href="{{route('admin.send_to_JordanNbs',$order->id)}}" class="print-hidden"> Send the Invoice to Jordan NBS</a>
@endif

<div id="invoice">
    <div id="header">
        <img id="logo" src="{{asset('assets/admin/imgs/logo.png')}}" alt="Company Logo">
        <div id="company-name">Kertas Company</div>
    </div>
    <br>
    <br>
    @if($order->order_type == 2)
    <h4 style="text-align: center !important;">Return</h4>
    @endif

    <div id="details">
        <div id="details-left">
            <p>Date: {{ \Carbon\Carbon::parse($order->date)->format('Y-m-d') }}</p>
            <p>Invoice #: {{$order->number}}</p>
            <p>Tax Number: 178196061 </p>
        </div>
        <div id="details-right">
            <p>Client: {{$order->user->name}}</p>
            <p class="inoice-d-address">Address: {{$order->address->address}} / Street: {{$order->address->street}} <br> Building Number: {{$order->address->building_number}}</p>
        </div>
    </div>

    <table id="products">
        <thead>
            <tr>
                <th>Image</th>
                <th>Product Name</th>
                <th>Quantity</th>
                <th>Unit Description</th>
                <th>Unit Price Before Tax</th>
                <th>Price Before Tax</th>
                <th>Discount Percentage</th>
                <th>Discount Value</th>
                <th>Tax</th>
                <th>Tax Amount</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->products as $product)
            <tr>
                <td>
                    @if ($product->productImages->first() && $product->productImages->first()->photo)
                    <div class="image">
                        <img class="custom_photo" src="{{ asset('assets/admin/uploads').'/'.$product->productImages->first()->photo }}">
                    </div>
                    @else
                    No Photo
                    @endif
                </td>
                <td>{{ $product->name_en }}</td>
               
                <td>{{ $product->pivot->quantity }}</td>
                <td>
                    @if ($product->pivot->unit_id)
                    {{ \App\Models\Unit::find($product->pivot->unit_id)->name_en }}
                    @else
                    None
                    @endif
                </td>
                <td>{{ round($product->pivot->total_price_before_tax / $product->pivot->quantity, 3) }}</td>
                <td>{{ round($product->pivot->total_price_before_tax, 3) }}</td>
                
                  
                <td>{{ $product->pivot->line_discount_percentage ?? 0 }} %</td>
                  
                  
                  
                <td>{{ round($product->pivot->line_discount_value,3) ?? 0 }}</td>
                  
                   
                <td>{{ $product->pivot->tax_percentage }} %</td>
               @php
                $discounted_price = $product->pivot->total_price_before_tax - $product->pivot->line_discount_value; 
                $price_after_coupon = $discounted_price - ($discounted_price * ($order->coupon_discount / 100));
                $tax_amount = $price_after_coupon * ($product->pivot->tax_percentage / 100);
            @endphp
               <td>{{ round($tax_amount, 3) }}</td>

                <td>{{ round($product->pivot->total_price_after_tax, 3) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>


    <div id="totals">
        @php
        $totalPriceBeforeTax = $order->products->sum(function ($product) {
            return $product->pivot->total_price_before_tax;
        });
        @endphp
        <div>Total Before Tax: {{ round($totalPriceBeforeTax, 3) }} JD</div>

        <div>
            @if ($order->total_discounts)
            <p class="total-label" style="color: red">Discount: - {{ round($order->total_discounts,3) }} JD</p>
            @endif
        </div>

        @php
        $taxGroups = $order->products->groupBy('pivot.tax_percentage')->map(function ($group) {
            return $group->sum('pivot.tax_value');
        });
        @endphp

        @foreach ($taxGroups as $taxPercentage => $taxValue)
        <div>Tax ({{ $taxPercentage }}%): {{ round($taxValue, 3) }} JD</div>
        @endforeach

        <div>Delivery Fee: {{ $order->delivery_fee }} JD</div>

        <div>Total: {{ round($order->total_prices, 3) }} JD</div>
    </div>

    @if($order->qr_code)
           
           <div class="card-body text-center">
               <!-- Generate QR code image from base64 data -->
               {!! QrCode::size(120)->generate($order->qr_code) !!}
           </div>
      
       @endif
</div>
@endsection

@section('script')
<script>
    function printInvoice() {
        window.print();
    }
</script>
@endsection
