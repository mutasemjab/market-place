@extends('layouts.admin')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-header">
                    <h4>{{ __('messages.Edit_ShopCategory') }}</h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('shop-categories.update', $category->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        
                        <div class="mb-3">
                            <label for="name_en" class="form-label">
                                {{ __('messages.Name_English') }}
                            </label>
                            <input type="text" 
                                   class="form-control @error('name_en') is-invalid @enderror" 
                                   id="name_en" 
                                   name="name_en" 
                                   value="{{ old('name_en', $category->name_en) }}" 
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
                                   value="{{ old('name_ar', $category->name_ar) }}" 
                                   placeholder="{{ __('messages.Enter_Arabic_Name') }}"
                                   required>
                            @error('name_ar')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                            <div class="mb-3">
                            <label for="cities" class="form-label">
                                {{ __('messages.Select_Cities') }}
                            </label>
                            <select name="cities[]" 
                                    id="cities" 
                                    class="form-control @error('cities') is-invalid @enderror" 
                                    multiple 
                                    required>
                                @foreach($cities as $city)
                                    <option value="{{ $city->id }}" 
                                            {{ in_array($city->id, old('cities', $category->cities->pluck('id')->toArray())) ? 'selected' : '' }}>
                                        {{ $city->name_en }} - {{ $city->name_ar }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="form-text text-muted">
                                {{ __('messages.Hold_Ctrl_Multiple_Select') }}
                            </small>
                            @error('cities')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        
                          <div class="col-md-12">
                                <div class="form-group">
                                    <img src="" id="image-preview" alt="Selected Image" height="50px" width="50px"
                                        style="display: none;">
                                    <button class="btn"> photo</button>
                                    <input type="file" id="Item_img" name="photo" class="form-control" onchange="previewImage()">
                                    @error('photo')
                                    <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                {{ __('messages.Update') }}
                            </button>
                            <a href="{{ route('shop-categories.index') }}" class="btn btn-secondary">
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