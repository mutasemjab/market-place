@extends('layouts.admin')
@section('title')
{{ __('messages.orders') }}
@endsection


@section('contentheaderactive')
{{ __('messages.Show') }}
@endsection



@section('content')



    <div class="card">
        <div class="card-header">
            <h3 class="card-title card_title_center"> {{ __('messages.orders') }}
            </h3>
            <input type="hidden" id="token_search" value="{{ csrf_token() }}">

            <a href="{{ route('orders.create') }}" class="btn btn-sm btn-success"> {{ __('messages.New') }}
                {{ __('messages.orders') }}</a>

                <form action="{{ route('orders.index') }}" method="GET" class="form-inline float-right">
                    <input type="text" name="search" class="form-control mr-sm-2" placeholder="{{ __('messages.Search') }}">
                    <button type="submit" class="btn btn-primary">{{ __('messages.Search') }}</button>
                </form>
        </div>
        <!-- /.card-header -->
        <div class="card-body">
            <div class="row">

            </div>
            <div class="clearfix"></div>

            <div id="ajax_responce_serarchDiv" class="col-md-12">

                @if (isset($data) && !$data->isEmpty())
                    <table id="example2" class="table table-bordered table-hover">
                        <thead class="custom_thead">
                            <th>#{{ __('messages.ID') }}</th>
                            <th>{{ __('messages.Date') }}</th>
                            <th>{{ __('messages.order_type') }}</th>
                            <th>{{ __('messages.order_status') }}</th>
                            <th>{{ __('messages.delivery_fee') }}</th>
                            <th>{{ __('messages.total_prices') }}</th>
                            <th>{{ __('messages.total_discount') }}</th>
                            <th>{{ __('messages.payment_type') }}</th>
                            <th>{{ __('messages.payment_status') }}</th>
                            <th>{{ __('messages.Action') }}</th>
                        </thead>
                        <tbody>
                            @foreach ($data as $info)
                                <tr>

                                    <td>{{ $info->number }}</td>
                                    <td>{{ $info->date }}</td>
                                    <td style="{{ $info->order_type == 2 ? 'color:red;' : '' }}">
                                        {{ $info->order_type == 1 ? __('Sell') : __('Refund') }}
                                    </td>

                                    <td>

                                        @if ($info->order_status == 1)
                                        {{ __('messages.Pending') }}
                                        @elseif($info->order_status == 2)
                                        {{ __('messages.Accepted') }}
                                        @elseif($info->order_status == 3)
                                        {{ __('messages.OnTheWay') }}
                                        @elseif($info->order_status == 4)
                                        {{ __('messages.Delivered') }}
                                        @elseif($info->order_status == 5)
                                        {{ __('messages.Canceled') }}
                                        @else
                                        {{ __('messages.Refund') }}
                                        @endif
                                    </td>
                                    <td>{{ $info->delivery_fee }}</td>
                                    <td>{{ $info->total_prices }}</td>
                                    <td>{{ $info->total_discounts }}</td>
                                    <td>{{ $info->payment_type == 1 ?  'cash' : 'visa' }}</td>
                                    <td>
                                        @if ($info->payment_status == 1)
                                            Paid
                                        @else
                                            UnPaid
                                        @endif
                                    </td>


                                    <td>
                                        <a href="{{ route('orders.edit', $info->id) }}"
                                            class="btn btn-sm btn-primary"> {{ __('messages.Edit') }}</a>
                                        <a href="{{ route('orders.show', $info->id) }}"
                                            class="btn btn-sm btn-primary"> {{ __('messages.Show') }}</a>
                                 <!--       <form action="{{ route('orders.destroy', $info->id) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger"> {{ __('messages.Delete') }}</button>
                                        </form>-->
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <br>
                    @if (method_exists($data, 'links'))
                        {{ $data->links() }}
                    @endif
                @else
                    <div class="alert alert-danger">
                        {{ __('messages.no_data') }}
                    </div>
                @endif

            </div>



        </div>

    </div>

    </div>

@endsection

@section('script')
    <script src="{{ asset('assets/admin/js/orderss.js') }}"></script>
@endsection
