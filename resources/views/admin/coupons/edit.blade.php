@extends('layouts.admin')
@section('title')
{{ __('messages.Edit') }} {{ __('messages.coupons') }}
@endsection



@section('contentheaderlink')
    <a href="{{ route('coupons.index') }}"> {{ __('messages.coupons') }} </a>
@endsection

@section('contentheaderactive')
{{ __('messages.Edit') }}
@endsection


@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title card_title_center"> {{ __('messages.Edit') }}  {{ __('messages.coupons') }} </h3>
        </div>
        <!-- /.card-header -->
        <div class="card-body">


            <form action="{{ route('coupons.update', $data['id']) }}" method="post" enctype='multipart/form-data'>
                <div class="row">
                    @csrf
                    @method('PUT')


                    <div class="col-md-6">
                        <div class="form-group">
                            <label> {{ __('messages.Code') }}</label>
                            <input name="code" id="code" class="form-control"
                                value="{{ old('code', $data['code']) }}">
                            @error('code')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label> {{ __('messages.discount_percentage') }} </label>
                            <input name="amount" id="amount" class="form-control"
                                value="{{ old('amount', $data['amount']) }}">
                            @error('amount')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>  {{ __('messages.Minimum_Total') }}</label>
                            <input name="minimum_total" id="minimum_total" class="form-control"
                                value="{{ old('minimum_total', $data['minimum_total']) }}">
                            @error('minimum_total')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label> {{ __('messages.Expired_At') }}</label>
                            <input  type="date" name="expired_at" id="expired_at" class="form-control"
                                value="{{ old('expired_at', $data['expired_at']) }}">
                            @error('expired_at')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>




                    <div class="col-md-12">
                        <div class="form-group text-center">
                            <button id="do_add_item_cardd" type="submit" class="btn btn-primary btn-sm"> {{ __('messages.Update') }}</button>
                            <a href="{{ route('coupons.index') }}" class="btn btn-sm btn-danger">{{ __('messages.Cancel') }}</a>

                        </div>
                    </div>

                </div>
            </form>



        </div>




    </div>
    </div>
@endsection


@section('script')
@endsection
