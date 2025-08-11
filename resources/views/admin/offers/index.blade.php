@extends('layouts.admin')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4>{{ __('messages.offers') }}</h4>
                    <a href="{{ route('offers.create') }}" class="btn btn-primary">
                        {{ __('messages.Add_New') }}
                    </a>
                </div>

                <div class="card-body">
                   

                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>{{ __('messages.id') }}</th>
                                    <th>{{ __('messages.price') }}</th>
                                    <th>{{ __('messages.start_date') }}</th>
                                    <th>{{ __('messages.expiry_date') }}</th>
                                    <th>{{ __('messages.product') }}</th>
                                    <th>{{ __('messages.shop') }}</th>
                                    <th>{{ __('messages.status') }}</th>
                                    <th>{{ __('messages.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($offers as $offer)
                                    <tr>
                                        <td>{{ $offer->id }}</td>
                                        <td>${{ number_format($offer->price, 2) }}</td>
                                        <td>{{ $offer->start_at->format('Y-m-d') }}</td>
                                        <td>{{ $offer->expired_at->format('Y-m-d') }}</td>
                                        <td>{{ $offer->product->name ?? __('messages.not_specified') }}</td>
                                        <td>{{ $offer->shop->name ?? __('messages.not_specified') }}</td>
                                        <td>
                                            @if($offer->isActive())
                                                <span class="badge bg-success">{{ __('messages.active') }}</span>
                                            @elseif($offer->isExpired())
                                                <span class="badge bg-danger">{{ __('messages.expired') }}</span>
                                            @else
                                                <span class="badge bg-warning">{{ __('messages.upcoming') }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                               
                                                <a href="{{ route('offers.edit', $offer) }}" 
                                                   class="btn btn-sm btn-warning">
                                                    {{ __('messages.edit') }}
                                                </a>
                                                <form action="{{ route('offers.destroy', $offer) }}" 
                                                      method="POST" class="d-inline"
                                                      onsubmit="return confirm('{{ __('messages.confirm_delete') }}')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger">
                                                        {{ __('messages.delete') }}
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center">{{ __('messages.no_offers_found') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{ $offers->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection