@extends('layouts.admin')
@section('title')
    {{ __('messages.offers') }}
@endsection


@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title card_title_center"> {{ __('messages.New') }}{{ __('messages.offers') }} </h3>
        </div>
        <!-- /.card-header -->
        <div class="card-body">


            <form action="{{ route('offers.store') }}" method="post" enctype='multipart/form-data'>
                <div class="row">
                    @csrf


                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="user_type">{{ __('messages.user_type') }}</label>
                            <select name="user_type" class="form-control" required>
                                <option value="1">{{ __('messages.User') }}</option>
                                <option value="2">{{ __('messages.wholeSales') }}</option>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label> {{ __('messages.Price') }}</label>
                            <input name="price" id="price" class="form-control" value="{{ old('price') }}">
                            @error('price')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label> {{ __('messages.start_at') }}</label>
                            <input type="date" name="start_at" id="price" class="form-control"
                                value="{{ old('start_at') }}">
                            @error('start_at')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label> {{ __('messages.end_at') }}</label>
                            <input type="date" name="expired_at" id="price" class="form-control"
                                value="{{ old('expired_at') }}">
                            @error('expired_at')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>



                    <div class="form-group col-md-6">
                        <label for="product_id">{{ __('messages.products') }}</label>
                        <select class="form-control" name="product" id="product_id">
                            <option value="">Select Product</option>
                            @foreach ($products as $product)
                                <option value="{{ $product->id }}">{{ $product->name_ar }}</option>
                            @endforeach
                        </select>
                        @error('product')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>{{ __('messages.selling_price') }}</label>
                            <div id="selling_price_display">N/A</div> <!-- Display area for the selling price -->
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>{{ __('messages.selling_price_for_user') }}</label>
                            <div id="selling_price_for_user_display">N/A</div> <!-- Display area for selling_price_for_user -->
                        </div>
                    </div>

                    <div class="col-md-12">
                        <div class="form-group text-center">
                            <button id="do_add_item_cardd" type="submit" class="btn btn-primary btn-sm">
                                {{ __('messages.Submit') }}</button>
                            <a href="{{ route('offers.index') }}"
                                class="btn btn-sm btn-danger">{{ __('messages.Cancel') }}</a>

                        </div>
                    </div>

                </div>
            </form>



        </div>




    </div>
    </div>
@endsection

@section('script')
<script>
    $(document).ready(function() {
        $('#product_id').select2({
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

        // Event listener for when a product is selected
        $('#product_id').on('select2:select', function (e) {
            var productId = e.params.data.id; // Get selected product ID

            // AJAX request to get selling prices for the selected product
            $.ajax({
                url: '/products/get-prices/' + productId, // Create a new route for this purpose
                method: 'GET',
                success: function(response) {
                    if (response.selling_price) {
                        $('#selling_price_display').text(response.selling_price); // Display the selling price
                    } else {
                        $('#selling_price_display').text('N/A'); // Default if no selling price is found
                    }

                    if (response.selling_price_for_user) {
                        $('#selling_price_for_user_display').text(response.selling_price_for_user); // Display selling_price_for_user
                    } else {
                        $('#selling_price_for_user_display').text('N/A'); // Default if no selling_price_for_user is found
                    }
                },
                error: function() {
                    $('#selling_price_display').text('N/A'); // Handle error
                    $('#selling_price_for_user_display').text('N/A'); // Handle error
                }
            });
        });

        // Initialize the display with N/A
        $('#selling_price_display').text('N/A');
        $('#selling_price_for_user_display').text('N/A');
    });
</script>
@endsection


