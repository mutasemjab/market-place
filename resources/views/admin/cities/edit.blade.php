@extends('layouts.admin')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-header">
                    <h4>{{ __('messages.Edit_City') }}</h4>
                </div>
                <div class="card-body">
                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('cities.update', $city->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="mb-3">
                            <label for="name_en" class="form-label">
                                {{ __('messages.Name_English') }} <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   class="form-control @error('name_en') is-invalid @enderror" 
                                   id="name_en" 
                                   name="name_en" 
                                   value="{{ old('name_en', $city->name_en) }}" 
                                   placeholder="{{ __('messages.Enter_English_Name') }}"
                                   required>
                            @error('name_en')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="name_ar" class="form-label">
                                {{ __('messages.Name_Arabic') }} <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   class="form-control @error('name_ar') is-invalid @enderror" 
                                   id="name_ar" 
                                   name="name_ar" 
                                   value="{{ old('name_ar', $city->name_ar) }}" 
                                   placeholder="{{ __('messages.Enter_Arabic_Name') }}"
                                   required>
                            @error('name_ar')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        {{-- Show associated categories if any --}}
                        @if($city->categories->count() > 0)
                            <div class="mb-3">
                                <label class="form-label">{{ __('messages.Associated_Categories') }}</label>
                                <div class="border rounded p-3 bg-light">
                                    @foreach($city->categories as $category)
                                        <span class="badge bg-info me-2 mb-2">
                                            {{ $category->name_en }} / {{ $category->name_ar }}
                                        </span>
                                    @endforeach
                                </div>
                                <small class="text-muted">
                                    {{ __('messages.Categories_Using_This_City') }}
                                </small>
                            </div>
                        @endif

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> {{ __('messages.Update') }}
                            </button>
                            <a href="{{ route('cities.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> {{ __('messages.Cancel') }}
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection