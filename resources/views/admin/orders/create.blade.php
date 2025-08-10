@extends('layouts.admin')

@section('content')
<div class="container">
    <h2>Create Invoice</h2>
    <form action="{{ route('orders.store') }}" method="POST">
        @csrf

        <input type="hidden" name="redirect_to" id="redirect_to" value="index">

        <button type="submit" class="btn btn-primary" onclick="setRedirect('index')">{{ __('messages.Submit') }}</button>
        <button type="submit" class="btn btn-primary" onclick="setRedirect('show')">{{ __('messages.Save_Print') }}</button>

        <div class="col-md-6">
            <div class="form-group mt-3">
                <label for="order_type">{{ __('messages.order_type') }}</label>
                <select name="order_type" class="form-control" required>
                    <option value="">select</option>
                    <option value="1">{{ __('messages.Sell') }}</option>
                    <option value="2">{{ __('messages.Refund') }}</option>
                </select>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label for="date">{{ __('messages.Date') }}</label>
                <input type="date" name="date" class="form-control" required>
            </div>
        </div>

        <div class="col-md-6">
            <div class="form-group mt-3">
                <label for="shop">{{ __('messages.shops') }}</label>
                <select name="shop" class="form-control" required>
                    @foreach ($shops as $shop)
                    <option value="{{ $shop->id }}">{{ $shop->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="col-md-6">
            <div class="form-group mt-3">
                <label for="payment_type">{{ __('messages.payment_type') }}</label>
                <select name="payment_type" class="form-control" required>
                    <option value="1">{{ __('messages.Cash') }}</option>
                    <option value="2">{{ __('messages.Receivables') }}</option>
                </select>
            </div>
        </div>

        <div class="col-md-6">
            <div class="form-group mt-3">
                <label for="userSearch">{{ __('messages.User') }}</label>
                <input type="text" id="userSearch" class="form-control" name="user" placeholder="{{ __('messages.search_customer') }}" required />
            </div>
        </div>

        <div class="col-md-6">
            <div class="form-group mt-3">
                <label for="address">{{ __('messages.Address') }}</label>
                <select id="addressSelect" class="form-control" name="address" required>
                    <option value="">{{ __('messages.select_address') }}</option>
                    <!-- Options will be populated by JavaScript -->
                </select>
            </div>
        </div>

        <div class="col-md-6">
            <div class="form-group mt-3">
                <label for="coupon_discount">{{ __('messages.Coupon Discount (%)') }}</label>
                <input type="number" name="coupon_discount" class="form-control" step="0.01" id="coupon_discount_input" required>
            </div>
        </div>

        <br>
        <table class="table table-bordered" id="products_table">
            <thead>
                <tr>
                    <th>{{ __('messages.product') }}</th>
                    <th>{{ __('messages.unit') }}</th>
                    <th>{{ __('messages.quantity') }}</th>
                    <th>{{ __('messages.selling_price_without_tax') }}</th>
                    <th>{{ __('messages.selling_price_with_tax') }}</th>
                    <th>{{ __('messages.tax') }}</th>
                    <th>{{ __('messages.discount_fixed') }}</th>
                    <th>{{ __('messages.discount_percentage') }}</th>
                    <th>{{ __('messages.total_one_item') }}</th>
                    <th>{{ __('messages.Action') }}</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><input type="text" class="form-control product-search" name="products[0][name]" /></td>
                    <td>
                        <select class="form-control product-unit" name="products[0][unit]">
                            <option value="">Select Unit</option>
                        </select>
                    </td>
                    <td><input type="number" class="form-control quantity" name="products[0][quantity]" /></td>
                    <td><input type="number" class="form-control selling_price_without_tax" name="products[0][selling_price_without_tax]" step="any" /></td>
                    <td><input type="number" class="form-control selling_price_with_tax" name="products[0][selling_price_with_tax]" step="any" /></td>
                    <td><input type="number" class="form-control tax" name="products[0][tax]" step="any" /></td>
                    <td><input type="number" class="form-control discount_fixed" name="products[0][discount_fixed]" step="any" /></td>
                    <td><input type="number" class="form-control discount_percentage" name="products[0][discount_percentage]" step="any" /></td>
                    <td><input type="number" class="form-control total_one_item" name="products[0][total_one_item]" step="any" /></td>
                    <td><button type="button" class="btn btn-danger remove-row">{{ __('messages.Delete') }}</button></td>
                </tr>
            </tbody>
        </table>

        <button type="button" class="btn btn-primary" id="add_row">{{ __('messages.Add_Row') }}</button>

        <!-- Summary Table -->
        <div class="mt-5">
            <h4>Summary</h4>
            <table class="table table-bordered" id="summary_table">
                <tbody>
                    <tr>
                        <td>{{ __('messages.total_selling_price') }}</td>
                        <td><span id="total_selling_price">0.00</span></td>
                    </tr>
                    <tr>
                        <td>{{ __('messages.total_tax') }}</td>
                        <td><span id="total_tax">0.00</span></td>
                    </tr>
                    <tr>
                        <td>{{ __('messages.total_discount') }}</td>
                        <td><span id="total_discount">0.00</span></td>
                    </tr>
                    <tr>
                        <td>{{ __('messages.coupon_discount') }}</td>
                        <td><span id="coupon_discount">0.00</span></td>
                    </tr>
                    <tr>
                        <td>{{ __('messages.delivery_fee') }}</td>
                        <td><span id="delivery_fee">0.00</span></td>
                    </tr>
                    <tr>
                        <td>{{ __('messages.total_amount') }}</td>
                        <td><span id="total_amount">0.00</span></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </form>
</div>
@endsection

@section('js')
<script type="text/javascript">
    function setRedirect(value) {
        document.getElementById('redirect_to').value = value;
    }

    $(document).ready(function() {
        // Initialize user search autocomplete
        $('#userSearch').autocomplete({
            source: function(request, response) {
                $.ajax({
                    url: '{{ route("search.users") }}',
                    dataType: 'json',
                    data: {
                        q: request.term
                    },
                    success: function(data) {
                        response($.map(data, function(user) {
                            return {
                                label: user.name,
                                value: user.name,
                                id: user.id
                            };
                        }));
                    }
                });
            },
            minLength: 2,
            select: function(event, ui) {
                $('#userSearch').data('selected-user-id', ui.item.id);
                fetchAddresses(ui.item.id);
            }
        });

        function fetchAddresses(userId) {
            if (!userId) {
                document.getElementById('addressSelect').innerHTML = '<option value="">{{ __('messages.select_address') }}</option>';
                return;
            }

            fetch(`{{ url('user') }}/${userId}/addresses`)
                .then(response => response.json())
                .then(data => {
                    const addressSelect = document.getElementById('addressSelect');
                    addressSelect.innerHTML = '<option value="">{{ __('messages.select_address') }}</option>';

                    data.forEach(address => {
                        const option = document.createElement('option');
                        option.value = address.id;
                        option.textContent = address.address;
                        option.dataset.deliveryId = address.delivery_id;
                        addressSelect.appendChild(option);
                    });

                    // Update the delivery fee when an address is selected
                    $('#addressSelect').on('change', function() {
                        const selectedOption = $(this).find('option:selected');
                        const deliveryId = selectedOption.data('deliveryId');
                        fetchDeliveryFee(deliveryId);
                    });
                })
                .catch(error => {
                    console.error('Error fetching addresses:', error);
                });
        }

        function fetchDeliveryFee(deliveryId) {
            fetch(`{{ url('delivery') }}/${deliveryId}/fee`)
                .then(response => response.json())
                .then(data => {
                    $('#delivery_fee').text(data.price.toFixed(2));
                    updateSummary();
                })
                .catch(error => {
                    console.error('Error fetching delivery fee:', error);
                });
        }

        let rowIdx = 1;

        $('#add_row').on('click', function() {
            $('#products_table tbody').append(`
                <tr>
                    <td><input type="text" class="form-control product-search" name="products[${rowIdx}][name]" /></td>
                    <td>
                        <select class="form-control product-unit" name="products[${rowIdx}][unit]">
                            <option value="">Select Unit</option>
                        </select>
                    </td>
                    <td><input type="number" class="form-control quantity" name="products[${rowIdx}][quantity]" /></td>
                    <td><input type="number" class="form-control selling_price_without_tax" name="products[${rowIdx}][selling_price_without_tax]" step="any" /></td>
                    <td><input type="number" class="form-control selling_price_with_tax" name="products[${rowIdx}][selling_price_with_tax]" step="any" /></td>
                    <td><input type="number" class="form-control tax" name="products[${rowIdx}][tax]" step="any" /></td>
                    <td><input type="number" class="form-control discount_fixed" name="products[${rowIdx}][discount_fixed]" step="any" /></td>
                    <td><input type="number" class="form-control discount_percentage" name="products[${rowIdx}][discount_percentage]" step="any" /></td>
                    <td><input type="number" class="form-control total_one_item" name="products[${rowIdx}][total_one_item]" step="any" /></td>
                    <td><button type="button" class="btn btn-danger remove-row">{{ __('messages.Delete') }}</button></td>
                </tr>
            `);
            rowIdx++;
            initializeProductSearch();
        });

        $(document).on('click', '.remove-row', function() {
            $(this).closest('tr').remove();
            updateSummary();
        });

        function initializeProductSearch() {
            $('.product-search').autocomplete({
                source: function(request, response) {
                    $.ajax({
                        url: '{{ route("products.search") }}',
                        dataType: 'json',
                        data: {
                            term: request.term
                        },
                        success: function(data) {
                            if (data.length === 0) {
                                response([{ label: 'Not Found', value: '' }]);
                            } else {
                                response($.map(data, function(item) {
                                    return {
                                        label: item.name,
                                        value: item.name,
                                        units: item.units,
                                        unit: item.unit,
                                        id: item.id,
                                        tax: item.tax,
                                        selling_price: item.selling_price
                                    };
                                }));
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error("Error fetching products:", error);
                        }
                    });
                },
                minLength: 2,
                select: function(event, ui) {
                    if (ui.item.value === '') {
                        event.preventDefault();
                    } else {
                        const selectedRow = $(this).closest('tr');
                        const unitDropdown = selectedRow.find('.product-unit');
                        unitDropdown.empty();

                        if (ui.item.unit) {
                            unitDropdown.append(`<option value="${ui.item.unit.id}" data-selling-price="${ui.item.unit.selling_price}">${ui.item.unit.name}</option>`);
                        }

                        if (ui.item.units) {
                            $.each(ui.item.units, function(index, unit) {
                                unitDropdown.append(`<option value="${unit.id}" data-selling-price="${unit.selling_price}">${unit.name}</option>`);
                            });
                        }

                        const sellingPriceWithTax = parseFloat(ui.item.selling_price);
                        const taxPercentage = parseFloat(ui.item.tax);
                        const sellingPriceWithoutTax = sellingPriceWithTax / (1 + taxPercentage / 100);

                        selectedRow.find('.selling_price_without_tax').val(sellingPriceWithoutTax.toFixed(2));
                        selectedRow.find('.tax').val(taxPercentage);
                        selectedRow.find('.selling_price_with_tax').val(sellingPriceWithTax.toFixed(2));

                        updateSummary();

                        unitDropdown.on('change', function() {
                            const selectedOption = $(this).find('option:selected');
                            const selectedSellingPrice = parseFloat(selectedOption.data('selling-price'));
                            const tax = parseFloat(selectedRow.find('.tax').val());

                            const newSellingPriceWithoutTax = selectedSellingPrice / (1 + tax / 100);
                            selectedRow.find('.selling_price_without_tax').val(newSellingPriceWithoutTax.toFixed(2));
                            selectedRow.find('.selling_price_with_tax').val(selectedSellingPrice.toFixed(2));

                            updateSummary();
                        });
                    }
                }
            });
        }

        $(document).on('change', '.quantity, .selling_price_without_tax, .tax, .discount_fixed, .discount_percentage, #coupon_discount_input', function() {
            const row = $(this).closest('tr');
            const quantity = parseFloat(row.find('.quantity').val()) || 0;
            const sellingPriceWithoutTax = parseFloat(row.find('.selling_price_without_tax').val()) || 0;
            const taxPercentage = parseFloat(row.find('.tax').val()) || 0;

            const sellingPriceWithTax = sellingPriceWithoutTax * (1 + taxPercentage / 100);
            row.find('.selling_price_with_tax').val(sellingPriceWithTax.toFixed(2));

            updateSummary();
        });

        function updateSummary() {
            let totalBeforeTax = 0;
            let totalLineDiscount = 0;
            let totalCouponDiscount = 0;
            let totalTax = 0;
            let totalAmount = 0;
            let totalPriceAfterLineDiscounts = 0;

            const couponDiscountPercentage = parseFloat($('#coupon_discount_input').val()) || 0;
            const deliveryFee = parseFloat($('#delivery_fee').text()) || 0;

            $('#products_table tbody tr').each(function() {
                const quantity = parseFloat($(this).find('.quantity').val()) || 0;
                const sellingPriceWithoutTax = parseFloat($(this).find('.selling_price_without_tax').val()) || 0;
                const taxPercentage = parseFloat($(this).find('.tax').val()) || 0;
                const lineDiscountFixed = parseFloat($(this).find('.discount_fixed').val()) || 0;
                const lineDiscountPercentage = parseFloat($(this).find('.discount_percentage').val()) || 0;

                // Total price before line discount
                const totalPriceBeforeLineDiscount = sellingPriceWithoutTax * quantity;

                // Apply line discounts
                const totalLineDiscountValue = (totalPriceBeforeLineDiscount * (lineDiscountPercentage / 100)) + lineDiscountFixed;
                const totalPriceAfterLineDiscount = totalPriceBeforeLineDiscount - totalLineDiscountValue;

                // Calculate the price after applying coupon discount
                const totalCouponDiscountValue = totalPriceAfterLineDiscount * (couponDiscountPercentage / 100);
                const totalPriceAfterAllDiscounts = totalPriceAfterLineDiscount - totalCouponDiscountValue;

                // Calculate tax based on the new total price after all discounts
                const totalRowTax = totalPriceAfterAllDiscounts * (taxPercentage / 100);

                // Calculate the total price for the item including tax and discounts
                const totalOneItem = totalPriceAfterAllDiscounts + totalRowTax;

                // Set the value for the total_one_item input field
                $(this).find('.total_one_item').val(totalOneItem.toFixed(2));

                // Accumulate values for summary calculations
                totalBeforeTax += totalPriceBeforeLineDiscount;
                totalLineDiscount += totalLineDiscountValue;
                totalPriceAfterLineDiscounts += totalPriceAfterLineDiscount;
                totalTax += totalRowTax;
                totalAmount += totalOneItem;
            });

            // Calculate coupon discount on the total after line discounts
            totalCouponDiscount = totalPriceAfterLineDiscounts * (couponDiscountPercentage / 100);

            // Debug outputs to trace calculation values
            console.log("Total Before Tax: " + totalBeforeTax.toFixed(2));
            console.log("Total Line Discount: " + totalLineDiscount.toFixed(2));
            console.log("Total Coupon Discount: " + totalCouponDiscount.toFixed(2));
            console.log("Total Tax: " + totalTax.toFixed(2));
            console.log("Total Amount: " + totalAmount.toFixed(2));

            // Update the summary fields
            $('#total_selling_price').text(totalBeforeTax.toFixed(2));
            $('#total_discount').text(totalLineDiscount.toFixed(2));
            $('#coupon_discount').text(totalCouponDiscount.toFixed(2));
            $('#total_tax').text(totalTax.toFixed(2));
            $('#total_amount').text(totalAmount.toFixed(2));
        }

        initializeProductSearch();
    });
</script>
@endsection
