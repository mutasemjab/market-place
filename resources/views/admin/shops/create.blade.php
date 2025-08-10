@extends('layouts.admin')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4>{{ __('messages.Create_New_shop') }}</h4>
                    <a href="{{ route('shops.index') }}" class="btn btn-secondary">{{ __('messages.Back_to_shops') }}</a>
                </div>

                <div class="card-body">
                    <form action="{{ route('shops.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="row">
                            <!-- English Name -->
                            <div class="col-md-6 mb-3">
                                <label for="name_en" class="form-label">{{ __('messages.Name_English') }} *</label>
                                <input type="text" 
                                       class="form-control @error('name_en') is-invalid @enderror" 
                                       id="name_en" 
                                       name="name_en" 
                                       value="{{ old('name_en') }}" 
                                       required>
                                @error('name_en')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Arabic Name -->
                            <div class="col-md-6 mb-3">
                                <label for="name_ar" class="form-label">{{ __('messages.Name_Arabic') }} *</label>
                                <input type="text" 
                                       class="form-control @error('name_ar') is-invalid @enderror" 
                                       id="name_ar" 
                                       name="name_ar" 
                                       value="{{ old('name_ar') }}" 
                                       required>
                                @error('name_ar')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <!-- English Description -->
                            <div class="col-md-6 mb-3">
                                <label for="description_en" class="form-label">{{ __('messages.Description_English') }} *</label>
                                <textarea class="form-control @error('description_en') is-invalid @enderror" 
                                          id="description_en" 
                                          name="description_en" 
                                          rows="4" 
                                          required>{{ old('description_en') }}</textarea>
                                @error('description_en')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Arabic Description -->
                            <div class="col-md-6 mb-3">
                                <label for="description_ar" class="form-label">{{ __('messages.Description_Arabic') }} *</label>
                                <textarea class="form-control @error('description_ar') is-invalid @enderror" 
                                          id="description_ar" 
                                          name="description_ar" 
                                          rows="4" 
                                          required>{{ old('description_ar') }}</textarea>
                                @error('description_ar')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Specifications -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="specification_en" class="form-label">{{ __('messages.Specifications_English') }}</label>
                                <div id="spec_en_container">
                                    <div class="input-group mb-2">
                                        <input type="text" class="form-control" name="specification_en[]" placeholder="{{ __('messages.Specification') }}">
                                        <button type="button" class="btn btn-outline-secondary" onclick="addSpecification('en')">+</button>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="specification_ar" class="form-label">{{ __('messages.Specifications_Arabic') }}</label>
                                <div id="spec_ar_container">
                                    <div class="input-group mb-2">
                                        <input type="text" class="form-control" name="specification_ar[]" placeholder="{{ __('messages.Specification_Arabic_Placeholder') }}">
                                        <button type="button" class="btn btn-outline-secondary" onclick="addSpecification('ar')">+</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <!-- Number of Reviews -->
                            <div class="col-md-4 mb-3">
                                <label for="number_of_review" class="form-label">{{ __('messages.Number_of_Reviews') }} *</label>
                                <input type="text" 
                                       class="form-control @error('number_of_review') is-invalid @enderror" 
                                       id="number_of_review" 
                                       name="number_of_review" 
                                       value="{{ old('number_of_review') }}" 
                                       required>
                                @error('number_of_review')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Number of Rating -->
                            <div class="col-md-4 mb-3">
                                <label for="number_of_rating" class="form-label">{{ __('messages.Number_of_Rating') }} *</label>
                                <input type="text" 
                                       class="form-control @error('number_of_rating') is-invalid @enderror" 
                                       id="number_of_rating" 
                                       name="number_of_rating" 
                                       value="{{ old('number_of_rating') }}" 
                                       required>
                                @error('number_of_rating')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Delivery Time -->
                            <div class="col-md-4 mb-3">
                                <label for="time_of_delivery" class="form-label">{{ __('messages.Delivery_Time') }} *</label>
                                <input type="text" 
                                       class="form-control @error('time_of_delivery') is-invalid @enderror" 
                                       id="time_of_delivery" 
                                       name="time_of_delivery" 
                                       value="{{ old('time_of_delivery') }}" 
                                       placeholder="{{ __('messages.Delivery_Time_Placeholder') }}"
                                       required>
                                @error('time_of_delivery')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <!-- Category -->
                            <div class="col-md-4 mb-3">
                                <label for="category_id" class="form-label">{{ __('messages.Category') }} *</label>
                                <select class="form-control @error('category_id') is-invalid @enderror" 
                                        id="category_id" 
                                        name="category_id" 
                                        required 
                                        onchange="filterCities()">
                                    <option value="">{{ __('messages.Select_Category') }}</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" 
                                                {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                            {{ $category->name_ar }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('category_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- City -->
                            <div class="col-md-4 mb-3">
                                <label for="city_id" class="form-label">{{ __('messages.city') }} *</label>
                                <select class="form-control @error('city_id') is-invalid @enderror" 
                                        id="city_id" 
                                        name="city_id" 
                                        required>
                                    <option value="">{{ __('messages.Select_city') }}</option>
                                    <!-- Cities will be populated dynamically -->
                                </select>
                                @error('city_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- URL -->
                            <div class="col-md-4 mb-3">
                                <label for="url" class="form-label">URL *</label>
                                <input type="text" 
                                       class="form-control @error('url') is-invalid @enderror" 
                                       id="url" 
                                       name="url" 
                                       value="{{ old('url') }}" 
                                       required>
                                @error('url')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Photo -->
                            <div class="col-md-4 mb-3">
                                <label for="photo" class="form-label">{{ __('messages.Photo') }} *</label>
                                <input type="file" 
                                       class="form-control @error('photo') is-invalid @enderror" 
                                       id="photo" 
                                       name="photo" 
                                       accept="image/*"
                                       required>
                                @error('photo')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="{{ route('shops.index') }}" class="btn btn-secondary me-md-2">{{ __('messages.Cancel') }}</a>
                            <button type="submit" class="btn btn-primary">{{ __('messages.Create_shop') }}</button>
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
// Store category-city relationships
const categoryCities = @json($categories->keyBy('id')->map(function($category) {
    return $category->cities->map(function($city) {
        return [
            'id' => $city->id,
            'name_ar' => $city->name_ar,
            'name_en' => $city->name_en
        ];
    });
}));

function filterCities() {
    const categoryId = document.getElementById('category_id').value;
    const citySelect = document.getElementById('city_id');
    
    // Clear existing options
    citySelect.innerHTML = '<option value="">{{ __("messages.Select_city") }}</option>';
    
    if (categoryId && categoryCities[categoryId]) {
        const cities = categoryCities[categoryId];
        
        cities.forEach(function(city) {
            const option = document.createElement('option');
            option.value = city.id;
            option.textContent = city.name_ar;
            
            // Check if this was the old selected value
            if ('{{ old("city_id") }}' == city.id) {
                option.selected = true;
            }
            
            citySelect.appendChild(option);
        });
        
        // Enable city select
        citySelect.disabled = false;
    } else {
        // Disable city select if no category selected
        citySelect.disabled = true;
    }
}

// Load cities on page load if category is already selected (for old values)
document.addEventListener('DOMContentLoaded', function() {
    const categoryId = document.getElementById('category_id').value;
    if (categoryId) {
        filterCities();
    } else {
        document.getElementById('city_id').disabled = true;
    }
});

function addSpecification(lang) {
    const container = document.getElementById(`spec_${lang}_container`);
    const placeholder = lang === 'en' ? "{{ __('messages.Specification') }}" : "{{ __('messages.Specification_Arabic_Placeholder') }}";
    
    const newDiv = document.createElement('div');
    newDiv.className = 'input-group mb-2';
    newDiv.innerHTML = `
        <input type="text" class="form-control" name="specification_${lang}[]" placeholder="${placeholder}">
        <button type="button" class="btn btn-outline-danger" onclick="removeSpecification(this)">-</button>
    `;
    
    container.appendChild(newDiv);
}

function removeSpecification(button) {
    button.parentElement.remove();
}
</script>
@endsection