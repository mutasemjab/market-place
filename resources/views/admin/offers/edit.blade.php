@extends('layouts.admin')
@section('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@endsection

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card">
                <div class="card-header">
                    <h4>{{ __('messages.edit') }} {{ __('messages.offer') }}</h4>
                </div>

                <div class="card-body">
                    <form action="{{ route('offers.update', $offer) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="mb-3">
                            <label for="price" class="form-label">{{ __('messages.price') }} <span class="text-danger">*</span></label>
                            <input type="number" 
                                   class="form-control @error('price') is-invalid @enderror" 
                                   id="price" 
                                   name="price" 
                                   value="{{ old('price', $offer->price) }}"
                                   step="0.01" 
                                   min="0"
                                   required>
                            @error('price')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="start_at" class="form-label">{{ __('messages.start_date') }} <span class="text-danger">*</span></label>
                                    <input type="date" 
                                           class="form-control @error('start_at') is-invalid @enderror" 
                                           id="start_at" 
                                           name="start_at" 
                                           value="{{ old('start_at', $offer->start_at->format('Y-m-d')) }}"
                                           required>
                                    @error('start_at')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="expired_at" class="form-label">{{ __('messages.expiry_date') }} <span class="text-danger">*</span></label>
                                    <input type="date" 
                                           class="form-control @error('expired_at') is-invalid @enderror" 
                                           id="expired_at" 
                                           name="expired_at" 
                                           value="{{ old('expired_at', $offer->expired_at->format('Y-m-d')) }}"
                                           required>
                                    @error('expired_at')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="product_id" class="form-label">{{ __('messages.product') }}</label>
                            <select class="form-select @error('product_id') is-invalid @enderror" 
                                    id="product_id" 
                                    name="product_id">
                                @if($offer->product)
                                    <option value="{{ $offer->product->id }}" selected>{{ $offer->product->name }}</option>
                                @else
                                    <option value="">{{ __('messages.select_product') }}</option>
                                @endif
                            </select>
                            @error('product_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            
                            <!-- Product Price Display -->
                            <div class="mt-2">
                                <div class="row">
                                    <div class="col-md-6">
                                        <small class="text-muted">{{ __('messages.selling_price') }}:</small>
                                        <span id="selling_price_display" class="fw-bold text-success ms-1">N/A</span>
                                    </div>
                                    <div class="col-md-6">
                                        <small class="text-muted">{{ __('messages.selling_price_for_user') }}:</small>
                                        <span id="selling_price_for_user_display" class="fw-bold text-info ms-1">N/A</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="shop_id" class="form-label">{{ __('messages.shop') }}</label>
                            <select class="form-select @error('shop_id') is-invalid @enderror" 
                                    id="shop_id" 
                                    name="shop_id">
                                <option value="">{{ __('messages.select_shop') }}</option>
                                @foreach($shops as $shop)
                                    <option value="{{ $shop->id }}" 
                                            {{ (old('shop_id', $offer->shop_id) == $shop->id) ? 'selected' : '' }}>
                                        {{ $shop->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('shop_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('offers.index') }}" class="btn btn-secondary">
                                {{ __('messages.cancel') }}
                            </a>
                            <button type="submit" class="btn btn-primary">
                                {{ __('messages.update') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>



@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        $('#product_id').select2({
            placeholder: '{{ __("messages.select_product") }}',
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
                        $('#selling_price_display').text(' + parseFloat(response.selling_price).toFixed(2)); // Display the selling price
                    } else {
                        $('#selling_price_display').text('N/A'); // Default if no selling price is found
                    }

                    if (response.selling_price_for_user) {
                        $('#selling_price_for_user_display').text(' + parseFloat(response.selling_price_for_user).toFixed(2)); // Display selling_price_for_user
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

        // Clear prices when product is cleared
        $('#product_id').on('select2:clear', function (e) {
            $('#selling_price_display').text('N/A');
            $('#selling_price_for_user_display').text('N/A');
        });

        // Load prices for initially selected product (in edit mode)
        @if($offer->product_id)
            var initialProductId = {{ $offer->product_id }};
            $.ajax({
                url: '/products/get-prices/' + initialProductId,
                method: 'GET',
                success: function(response) {
                    if (response.selling_price) {
                        $('#selling_price_display').text(' + parseFloat(response.selling_price).toFixed(2));
                    } else {
                        $('#selling_price_display').text('N/A');
                    }

                    if (response.selling_price_for_user) {
                        $('#selling_price_for_user_display').text(' + parseFloat(response.selling_price_for_user).toFixed(2));
                    } else {
                        $('#selling_price_for_user_display').text('N/A');
                    }
                },
                error: function() {
                    $('#selling_price_display').text('N/A');
                    $('#selling_price_for_user_display').text('N/A');
                }
            });
        @else
            // Initialize the display with N/A
            $('#selling_price_display').text('N/A');
            $('#selling_price_for_user_display').text('N/A');
        @endif
    });
</script>
@endpush
@endsection