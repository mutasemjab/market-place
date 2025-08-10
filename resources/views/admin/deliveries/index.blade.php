@extends('layouts.admin')
@section('title')
{{ __('messages.deliveries') }}
@endsection






@section('content')



      <div class="card">
        <div class="card-header">
          <h3 class="card-title card_title_center"> {{ __('messages.deliveries') }} </h3>
          <input type="hidden" id="token_search" value="{{csrf_token() }}">

          <a href="{{ route('deliveries.create') }}" class="btn btn-sm btn-success" > {{ __('messages.New') }} {{ __('messages.deliveries') }}</a>
        </div>
        <!-- /.card-header -->
        <div class="card-body">
          <div class="row">
          <div class="col-md-4">

            {{-- <input  type="radio" name="searchbyradio" id="searchbyradio" value="name"> name --}}

            {{-- <input autofocus style="margin-top: 6px !important;" type="text" id="search_by_text" placeholder=" name" class="form-control"> <br> --}}

                      </div>

                          </div>
               <div class="clearfix"></div>

        <div id="ajax_responce_serarchDiv" class="col-md-12">
            @can('order-table')
            @if (isset($data) && !$data->isEmpty())
            <table id="example2" class="table table-bordered table-hover">
                <thead class="custom_thead">
                    <th>{{ __('messages.Place') }}</th>
                    <th>{{ __('messages.Price') }}</th>
                    <th>{{ __('messages.Action') }}</th>
                </thead>
                <tbody>
                    @foreach ($data as $info)
                    <tr>


                        <td>{{ $info->place }}</td>
                        <td>{{ $info->price }}</td>

                        <td>
                            @can('delivery-edit')
                            <a href="{{ route('deliveries.edit', $info->id) }}" class="btn btn-sm btn-primary">{{ __('messages.Edit') }}</a>
                            @endcan
                            @can('delivery-delete')
                            <form action="{{ route('deliveries.destroy', $info->id) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger">{{ __('messages.Delete') }}</button>
                            </form>
                            @endcan
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            <br>
            {{ $data->links() }}

            @else
            <div class="alert alert-danger">
                {{ __('messages.No_data') }} </div>
            </div>
            @endif
            @endcan
        </div>



      </div>

        </div>

</div>

@endsection

@section('script')
<script src="{{ asset('assets/admin/js/deliveriess.js') }}"></script>

@endsection


