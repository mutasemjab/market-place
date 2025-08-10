@extends('layouts.admin')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4>{{ __('messages.Cities_Management') }}</h4>
                    <a href="{{ route('cities.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> {{ __('messages.Add_City') }}
                    </a>
                </div>
                <div class="card-body">
                  

                    @if($cities->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th scope="col">#</th>
                                        <th scope="col">{{ __('messages.Name_English') }}</th>
                                        <th scope="col">{{ __('messages.Name_Arabic') }}</th>
                                        <th scope="col">{{ __('messages.Categories_Count') }}</th>
                                        <th scope="col">{{ __('messages.Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($cities as $city)
                                        <tr>
                                            <td>{{ $loop->iteration + ($cities->currentPage() - 1) * $cities->perPage() }}</td>
                                            <td>{{ $city->name_en }}</td>
                                            <td>{{ $city->name_ar }}</td>
                                            <td>
                                                <span class="badge bg-info">
                                                    {{ $city->categories->count() }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('cities.edit', $city->id) }}" 
                                                       class="btn btn-sm btn-warning">
                                                        <i class="fas fa-edit"></i> {{ __('messages.Edit') }}
                                                    </a>
                                                    <button type="button" 
                                                            class="btn btn-sm btn-danger" 
                                                            onclick="confirmDelete({{ $city->id }})">
                                                        <i class="fas fa-trash"></i> {{ __('messages.Delete') }}
                                                    </button>
                                                </div>
                                                
                                                <form id="delete-form-{{ $city->id }}" 
                                                      action="{{ route('cities.destroy', $city->id) }}" 
                                                      method="POST" 
                                                      style="display: none;">
                                                    @csrf
                                                    @method('DELETE')
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        {{-- Pagination --}}
                        <div class="d-flex justify-content-center">
                            {{ $cities->links() }}
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-city fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">{{ __('messages.No_Cities_Found') }}</h5>
                            <p class="text-muted">{{ __('messages.Start_Adding_Cities') }}</p>
                            <a href="{{ route('cities.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus"></i> {{ __('messages.Add_First_City') }}
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
    function confirmDelete(cityId) {
        if (confirm('{{ __("messages.Are_You_Sure_Delete_City") }}')) {
            document.getElementById('delete-form-' + cityId).submit();
        }
    }
</script>
@endsection