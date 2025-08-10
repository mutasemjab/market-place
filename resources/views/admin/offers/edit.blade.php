@extends('layouts.admin')
@section('title')
{{ __('messages.Edit') }}  {{ __('messages.offers') }}
@endsection

@section('contentheaderlink')
<a href="{{ route('offers.index') }}">  {{ __('messages.offers') }}  </a>
@endsection

@section('contentheaderactive')
{{ __('messages.Edit') }}
@endsection

@section('content')

<div class="card">
    <div class="card-header">
        <h3 class="card-title card_title_center"> {{ __('messages.Edit') }} {{ __('messages.offers') }} </h3>
    </div>
    <!-- /.card-header -->
    <div class="card-body">

        <form action="{{ route('offers.update', $data['id']) }}" method="post" enctype='multipart/form-data'>
            <div class="row">
                @csrf
                @method('PUT')

                <div class="col-md-6">
                    <div class="form-group">
                        <label for="user_type">{{ __('messages.user_type') }}</label>
                        <select name="user_type" id="user_type" class="form-control" required>
                            <option value="1" {{ $data->user_type == 1 ? 'selected' : '' }}>{{ __('messages.User') }}</option>
                            <option value="2" {{ $data->user_type == 2 ? 'selected' : '' }}>{{ __('messages.wholeSales') }}</option>
                        </select>
                    </div>
                </div>

                <div class="form-group col-md-6">
                    <label for="category_header">Select Product</label>
                    <select class="form-control" name="product" id="category_header">
                        <option value="">Select Product</option>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}" @if($data->product_id == $product->id) selected @endif>{{ $product->name_ar }}</option>
                        @endforeach
                    </select>
                    @error('product')
                    <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group col-md-6">
                    <label for="name">{{ __('messages.price') }} <span class="text-danger">*</span></label>
                    <input type="text" name="price" id="name" class="form-control" value="{{ $data->price }}">
                    @error('price')
                    <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group col-md-6">
                    <label for="name">{{ __('messages.start_at') }} <span class="text-danger">*</span></label>
                    <input type="date" name="start_at" id="name" class="form-control" value="{{ $data->start_at }}">
                    @error('start_at')
                    <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group col-md-6">
                    <label for="name"> {{ __('messages.end_at') }} <span class="text-danger">*</span></label>
                    <input type="date" name="expired_at" id="name" class="form-control" value="{{ $data->expired_at }}">
                    @error('expired_at')
                    <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group col-md-6">
                    <label>{{ __('messages.selling_price') }}</label>
                    <div id="selling_price_display">{{ $data->selling_price ?? 'N/A' }}</div> <!-- Display area for selling price -->
                </div>
                
                <div class="form-group col-md-6">
                    <label>{{ __('messages.selling_price_for_user') }}</label>
                    <div id="selling_price_for_user_display">{{ $data->selling_price_for_user ?? 'N/A' }}</div> <!-- Display area for selling_price_for_user -->
                </div>

                <div class="col-md-12">
                    <div class="form-group text-center">
                        <button id="do_add_item_cardd" type="submit" class="btn btn-primary btn-sm">{{ __('messages.Update') }} </button>
                        <a href="{{ route('offers.index') }}" class="btn btn-sm btn-danger">{{ __('messages.cancel') }}</a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@section('script')
<script>
    $(document).ready(function() {
        // Initialize Select2
        $('#category_header').select2({
            placeholder: 'Select Product',
            allowClear: true,
            minimumInputLength: 0,
            ajax: {
                url: '{{ route('products.search') }}',
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return {
                        term: params.term // search term
                    };
                },
                processResults: function(data) {
                    return {
                        results: $.map(data, function(item) {
                            return {
                                id: item.id,
                                text: item.name,
                            };
                        })
                    };
                },
                cache: true
            }
        });

        // Function to fetch and display prices
        function fetchPrices(productId) {
            $.ajax({
                url: '/products/get-prices/' + productId, // Existing route
                method: 'GET',
                success: function(response) {
                    $('#selling_price_display').text(response.selling_price ? response.selling_price : 'N/A');
                    $('#selling_price_for_user_display').text(response.selling_price_for_user ? response.selling_price_for_user : 'N/A');
                },
                error: function() {
                    $('#selling_price_display').text('N/A');
                    $('#selling_price_for_user_display').text('N/A');
                }
            });
        }

        // Fetch prices when a new product is selected
        $('#category_header').on('select2:select', function (e) {
            var productId = e.params.data.id; // Get selected product ID
            fetchPrices(productId);
        });

        // Fetch prices for the initially selected product
        var initialProductId = $('#category_header').val();
        if (initialProductId) {
            fetchPrices(initialProductId);
        }
    });
</script>
@endsection
