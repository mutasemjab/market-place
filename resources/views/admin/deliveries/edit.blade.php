@extends('layouts.admin')
@section('title')
    edit Delivery
@endsection



@section('contentheaderlink')
    <a href="{{ route('deliveries.index') }}"> Delivery </a>
@endsection

@section('contentheaderactive')
    Edit
@endsection


@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title card_title_center"> edit Delivery</h3>
        </div>
        <!-- /.card-header -->
        <div class="card-body">


            <form action="{{ route('deliveries.update', $data['id']) }}" method="post" enctype='multipart/form-data'>
                <div class="row">
                    @csrf
                    @method('PUT')


                    <div class="col-md-6">
                        <div class="form-group">
                            <label> Place Name</label>
                            <input name="place" id="place" class="form-control"
                                value="{{ old('place', $data['place']) }}">
                            @error('place')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label> Price</label>
                            <input name="price" id="price" class="form-control"
                                value="{{ old('price', $data['price']) }}">
                            @error('price')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    


                    <div class="col-md-12">
                        <div class="form-group text-center">
                            <button id="do_add_item_cardd" type="submit" class="btn btn-primary btn-sm"> update</button>
                            <a href="{{ route('deliveries.index') }}" class="btn btn-sm btn-danger">cancel</a>

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
