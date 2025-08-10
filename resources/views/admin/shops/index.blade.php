@extends('layouts.admin')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4>{{ __('messages.shops') }}</h4>
                    <a href="{{ route('shops.create') }}" class="btn btn-primary">{{ __('messages.Add_New_shop') }}</a>
                </div>

                <div class="card-body">
              

                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>{{ __('messages.ID') }}</th>
                                    <th>{{ __('messages.Photo') }}</th>
                                    <th>{{ __('messages.Name_English') }}</th>
                                    <th>{{ __('messages.Name_Arabic') }}</th>
                                    <th>{{ __('messages.Category') }}</th>
                                    <th>{{ __('messages.Reviews') }}</th>
                                    <th>{{ __('messages.Rating') }}</th>
                                    <th>{{ __('messages.Delivery_Time') }}</th>
                                    <th>{{ __('messages.Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($shops as $shop)
                                    <tr>
                                        <td>{{ $shop->id }}</td>
                                        <td>
                                            @if($shop->photo)
                                                <img src="{{ asset('assets/admin/uploads/' . $shop->photo) }}" 
                                                     alt="shop Image" 
                                                     class="img-thumbnail" 
                                                     style="width: 50px; height: 50px; object-fit: cover;">
                                            @else
                                                <span class="text-muted">{{ __('messages.No_Image') }}</span>
                                            @endif
                                        </td>
                                        <td>{{ $shop->name_en }}</td>
                                        <td>{{ $shop->name_ar }}</td>
                                        <td>{{ $shop->category->name ?? __('messages.N/A') }}</td>
                                        <td>{{ $shop->number_of_review }}</td>
                                        <td>{{ $shop->number_of_rating }}</td>
                                        <td>{{ $shop->time_of_delivery }}</td>
                                        <td>
                                            <div class="btn-group" role="group">
                                             
                                                <a href="{{ route('shops.edit', $shop) }}" 
                                                   class="btn btn-sm btn-warning">{{ __('messages.Edit') }}</a>
                                                <form action="{{ route('shops.destroy', $shop) }}" 
                                                      method="POST" 
                                                      style="display: inline;"
                                                      onsubmit="return confirm('{{ __('messages.Are_you_sure_delete_shop') }}')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger">{{ __('messages.Delete') }}</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center">{{ __('messages.No_shops_found') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-center">
                        {{ $shops->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection