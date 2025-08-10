@extends('layouts.admin')
@section('title')
{{ __('messages.deliveries') }}
@endsection


@section('content')

      <div class="card">
        <div class="card-header">
          <h3 class="card-title card_title_center"> {{ __('messages.New') }} {{ __('messages.deliveries') }}   </h3>
        </div>
        <!-- /.card-header -->
        <div class="card-body">


      <form action="{{ route('deliveries.store') }}" method="post" enctype='multipart/form-data'>
        <div class="row">
        @csrf

        <div class="col-md-6">
            <div class="form-group">
              <label>  {{ __('messages.Place') }}    </label>
              <input name="place" id="place" class="form-control" value="{{ old('place') }}"    >
              @error('place')
              <span class="text-danger">{{ $message }}</span>
              @enderror
            </div>
        </div>

        <div class="col-md-6">
            <div class="form-group">
              <label>   {{ __('messages.Price') }}  </label>
              <input name="price" id="price" class="form-control" value="{{ old('price') }}"    >
              @error('price')
              <span class="text-danger">{{ $message }}</span>
              @enderror
            </div>
        </div>




      <div class="col-md-12">
      <div class="form-group text-center">
        <button id="do_add_item_cardd" type="submit" class="btn btn-primary btn-sm">{{ __('messages.Submit') }} </button>
        <a href="{{ route('deliveries.index') }}" class="btn btn-sm btn-danger">{{ __('messages.Cancel') }}</a>

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






