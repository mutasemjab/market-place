@extends('layouts.admin')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-header">
                    <h4>{{ __('messages.Add_Category') }}</h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('categories.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        
                        <div class="mb-3">
                            <label for="name_en" class="form-label">
                                {{ __('messages.Name_English') }}
                            </label>
                            <input type="text" 
                                   class="form-control @error('name_en') is-invalid @enderror" 
                                   id="name_en" 
                                   name="name_en" 
                                   value="{{ old('name_en') }}" 
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
                                {{ __('messages.Name_Arabic') }}
                            </label>
                            <input type="text" 
                                   class="form-control @error('name_ar') is-invalid @enderror" 
                                   id="name_ar" 
                                   name="name_ar" 
                                   value="{{ old('name_ar') }}" 
                                   placeholder="{{ __('messages.Enter_Arabic_Name') }}"
                                   required>
                            @error('name_ar')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                         <div class="mb-3">
                            <label for="Shop" class="form-label">
                                {{ __('messages.Select_Shops') }}
                            </label>
                            <select name="shop" 
                                    id="shop" 
                                    class="form-control @error('Shop') is-invalid @enderror"  
                                    required>
                                @foreach($shops as $shop)
                                    <option value="{{ $shop->id }}" 
                                            {{ $shop->id ? 'selected' : '' }}>
                                        {{ $shop->name_en }} - {{ $shop->name_ar }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="form-text text-muted">
                                {{ __('messages.Hold_Ctrl_Multiple_Select') }}
                            </small>
                            @error('shop')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>



                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                {{ __('messages.Save') }}
                            </button>
                            <a href="{{ route('categories.index') }}" class="btn btn-secondary">
                                {{ __('messages.Cancel') }}
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection


@section('script')
<script>
    function previewImage() {
            var preview = document.getElementById('image-preview');
            var input = document.getElementById('Item_img');
            var file = input.files[0];
            if (file) {
                preview.style.display = "block";
                var reader = new FileReader();
                reader.onload = function() {
                    preview.src = reader.result;
                }
                reader.readAsDataURL(file);
            } else {
                preview.style.display = "none";
            }
        }
</script>
@endsection