@extends('layouts.admin')
@section('css')
<style>
.table th {
    background-color: #f8f9fa;
    font-weight: 600;
}

.btn-group .btn {
    margin-right: 2px;
}

.btn-group .btn:last-child {
    margin-right: 0;
}

.img-thumbnail {
    border-radius: 4px;
}
</style>
@endsection
@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Products Management</h3>
                    <a href="{{ route('products.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add New Product
                    </a>
                </div>
                
                <div class="card-body">
                   
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Photo</th>
                                    <th>Name (EN)</th>
                                    <th>Name (AR)</th>
                                    <th>Category</th>
                                    <th>Price</th>
                                    <th>Tax</th>
                                    <th>Status</th>
                                    <th>Featured</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($products as $product)
                                    <tr>
                                        <td>{{ $product->id }}</td>
                                        <td>
                                            @if($product->photos->isNotEmpty())
                                                <img src="{{ asset('assets/admin/uploads/' . $product->photos->first()->photo) }}" 
                                                     alt="{{ $product->name_en }}" 
                                                     class="img-thumbnail" 
                                                     style="width: 50px; height: 50px; object-fit: cover;">
                                            @else
                                                <div class="bg-light d-flex align-items-center justify-content-center" 
                                                     style="width: 50px; height: 50px;">
                                                    <i class="fas fa-image text-muted"></i>
                                                </div>
                                            @endif
                                        </td>
                                        <td>{{ $product->name_en }}</td>
                                        <td>{{ $product->name_ar }}</td>
                                        <td>{{ $product->category->name_en ?? 'N/A' }}</td>
                                        <td>${{ number_format($product->selling_price, 2) }}</td>
                                        <td>{{ $product->tax }}%</td>
                                        <td>
                                            <span class="badge badge-{{ $product->status ? 'success' : 'danger' }}">
                                                {{ $product->status ? 'Active' : 'Inactive' }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge badge-{{ $product->is_featured ? 'success' : 'secondary' }}">
                                                {{ $product->is_featured ? 'Featured' : 'Not Featured' }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('products.show', $product) }}" 
                                                   class="btn btn-info btn-sm" title="View">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('products.edit', $product) }}" 
                                                   class="btn btn-warning btn-sm" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form action="{{ route('products.destroy', $product) }}" 
                                                      method="POST" 
                                                      class="d-inline"
                                                      onsubmit="return confirm('Are you sure you want to delete this product?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger btn-sm" title="Delete">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="10" class="text-center">
                                            <p class="mb-0">No products found.</p>
                                            <a href="{{ route('products.create') }}" class="btn btn-primary btn-sm mt-2">
                                                Create First Product
                                            </a>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-center">
                        {{ $products->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


@endsection