@extends('layouts.admin')
@section('css')
<style>
.variation-row {
    border-left: 3px solid #007bff;
    padding-left: 15px;
    margin-left: 0;
    margin-right: 0;
}

.add-variation, .remove-variation {
    width: 100%;
}

.form-text.text-muted {
    font-size: 0.875rem;
}

.card-header .card-tools {
    margin-left: auto;
}
</style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Create New Product</h3>
                    <div class="card-tools">
                        <a href="{{ route('products.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Products
                        </a>
                    </div>
                </div>
                
                <form action="{{ route('products.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
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

                        <div class="row">
                            <!-- Basic Information -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="category_id">Category <span class="text-danger">*</span></label>
                                    <select name="category_id" id="category_id" class="form-control @error('category_id') is-invalid @enderror">
                                        <option value="">Select Category</option>
                                        @foreach($categories as $category)
                                            <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                                {{ $category->name_en }} ({{ $category->name_ar }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('category_id')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="name_en">Name (English) <span class="text-danger">*</span></label>
                                    <input type="text" name="name_en" id="name_en" 
                                           class="form-control @error('name_en') is-invalid @enderror" 
                                           value="{{ old('name_en') }}" required>
                                    @error('name_en')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="name_ar">Name (Arabic) <span class="text-danger">*</span></label>
                                    <input type="text" name="name_ar" id="name_ar" 
                                           class="form-control @error('name_ar') is-invalid @enderror" 
                                           value="{{ old('name_ar') }}" required dir="rtl">
                                    @error('name_ar')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="selling_price">Selling Price <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">$</span>
                                        </div>
                                        <input type="number" name="selling_price" id="selling_price" 
                                               class="form-control @error('selling_price') is-invalid @enderror" 
                                               value="{{ old('selling_price') }}" step="0.01" min="0" required>
                                        @error('selling_price')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="tax">Tax (%)</label>
                                            <input type="number" name="tax" id="tax" 
                                                   class="form-control @error('tax') is-invalid @enderror" 
                                                   value="{{ old('tax', 16) }}" step="0.01" min="0" max="100">
                                            @error('tax')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="min_order">Minimum Order <span class="text-danger">*</span></label>
                                            <input type="number" name="min_order" id="min_order" 
                                                   class="form-control @error('min_order') is-invalid @enderror" 
                                                   value="{{ old('min_order', 1) }}" min="1" required>
                                            @error('min_order')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Descriptions and Settings -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="description_en">Description (English) <span class="text-danger">*</span></label>
                                    <textarea name="description_en" id="description_en" 
                                              class="form-control @error('description_en') is-invalid @enderror" 
                                              rows="4" required>{{ old('description_en') }}</textarea>
                                    @error('description_en')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="description_ar">Description (Arabic) <span class="text-danger">*</span></label>
                                    <textarea name="description_ar" id="description_ar" 
                                              class="form-control @error('description_ar') is-invalid @enderror" 
                                              rows="4" required dir="rtl">{{ old('description_ar') }}</textarea>
                                    @error('description_ar')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <!-- Status Settings -->
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="status">Status <span class="text-danger">*</span></label>
                                            <select name="status" id="status" class="form-control @error('status') is-invalid @enderror" required>
                                                <option value="1" {{ old('status', '1') == '1' ? 'selected' : '' }}>Active</option>
                                                <option value="0" {{ old('status') == '0' ? 'selected' : '' }}>Inactive</option>
                                            </select>
                                            @error('status')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="is_featured">Featured</label>
                                            <select name="is_featured" id="is_featured" class="form-control">
                                                <option value="1" {{ old('is_featured', '1') == '1' ? 'selected' : '' }}>Yes</option>
                                                <option value="0" {{ old('is_featured') == '0' ? 'selected' : '' }}>No</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                  
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="best_selling">Best Selling</label>
                                            <select name="best_selling" id="best_selling" class="form-control">
                                                <option value="0" {{ old('best_selling', '0') == '0' ? 'selected' : '' }}>No</option>
                                                <option value="1" {{ old('best_selling') == '1' ? 'selected' : '' }}>Yes</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Photos Section -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <h5>Product Photos</h5>
                                <div class="form-group">
                                    <label for="photos">Upload Photos</label>
                                    <input type="file" name="photos[]" id="photos" 
                                           class="form-control-file @error('photos.*') is-invalid @enderror" 
                                           multiple accept="image/*">
                                    <small class="form-text text-muted">
                                        You can select multiple images. Supported formats: JPEG, PNG, JPG, GIF. Max size: 2MB per image.
                                    </small>
                                    @error('photos.*')
                                        <span class="invalid-feedback d-block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Variations Section -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <h5>Product Variations</h5>
                                <div id="variations-container">
                                    <div class="variation-row row mb-2">
                                        <div class="col-md-5">
                                            <input type="text" name="variation_names[]" 
                                                   class="form-control" placeholder="Variation name (e.g., Small, Medium, Large)">
                                        </div>
                                        <div class="col-md-5">
                                            <input type="number" name="variation_prices[]" 
                                                   class="form-control" placeholder="Price" step="0.01" min="0">
                                        </div>
                                        <div class="col-md-2">
                                            <button type="button" class="btn btn-success add-variation">
                                                <i class="fas fa-plus"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <small class="form-text text-muted">
                                    Add different variations of this product with their respective prices.
                                </small>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Create Product
                        </button>
                        <a href="{{ route('products.index') }}" class="btn btn-secondary">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@section('script')
<script>
$(document).ready(function() {
    // Add variation functionality
    $(document).on('click', '.add-variation', function() {
        var newRow = `
            <div class="variation-row row mb-2">
                <div class="col-md-5">
                    <input type="text" name="variation_names[]" 
                           class="form-control" placeholder="Variation name (e.g., Small, Medium, Large)">
                </div>
                <div class="col-md-5">
                    <input type="number" name="variation_prices[]" 
                           class="form-control" placeholder="Price" step="0.01" min="0">
                </div>
                <div class="col-md-2">
                    <button type="button" class="btn btn-danger remove-variation">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
        `;
        $('#variations-container').append(newRow);
    });

    // Remove variation functionality
    $(document).on('click', '.remove-variation', function() {
        $(this).closest('.variation-row').remove();
    });
});
</script>
@endsection