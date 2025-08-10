@extends('layouts.admin')
@section('title')
{{ __('messages.offers') }}
@endsection


@section('contentheaderactive')
{{ __('messages.Show') }}
@endsection



@section('content')



      <div class="card">
        <div class="card-header">
          <h3 class="card-title card_title_center"> {{ __('messages.offers') }} </h3>
          <input type="hidden" id="token_search" value="{{csrf_token() }}">

          <a href="{{ route('offers.create') }}" class="btn btn-sm btn-success" > {{ __('messages.New') }} {{ __('messages.offers') }}</a>
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

            @if (isset($data) && !$data->isEmpty())
            @can('offer-table')
            <table id="example2" class="table table-bordered table-hover">
                <thead class="custom_thead">

                    <th>{{ __('messages.name_of_product') }}</th>
                    <th>{{ __('messages.Price') }}</th>
                    <th>{{ __('messages.start_at') }}</th>
                    <th>{{ __('messages.end_at') }}</th>
                    <th>{{ __('messages.Action') }}</th>
                </thead>
                <tbody>
                    @foreach ($data as $info)
                    <tr>


                        <td>{{ $info->product->name_ar }}</td>

                        <td>
                            {{$info->price}}
                        </td>
                        <td>
                            {{$info->start_at}}
                        </td>
                        <td>
                            {{$info->expired_at}}
                        </td>
                        <td>
                            @can('offer-edit')
                            <a href="{{ route('offers.edit', $info->id) }}" class="btn btn-sm btn-primary">{{ __('messages.Edit') }}</a>
                           @endcan
                           @can('offer-delete')
                           <form action="{{ route('offers.destroy', $info->id) }}" method="POST">
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
            @endcan
            <br>
            {{ $data->links() }}

            @else
            <div class="alert alert-danger">
                {{ __('messages.No_data') }}

            </div>
            @endif

        </div>



      </div>

        </div>

</div>

@endsection

@section('script')
<script src="{{ asset('assets/admin/js/offerss.js') }}"></script>

@endsection


